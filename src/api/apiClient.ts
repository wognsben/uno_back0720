// apiClient.ts
// UNO Travel 프런트가 백엔드 API와 통신할 때 사용하는 공통 fetch 래퍼 파일이다.
// API base URL, JSON 직렬화, credentials 포함, 공통 성공/실패 응답 타입을 관리한다.
// 상품/예약 도메인별 API 파일이 fetch 세부 구현을 중복하지 않도록 하는 기반 역할이다.

import { createApiSecurityHeaders } from "./apiSecurity";

export type UnoApiErrorCode =
  | "LOGIN_REQUIRED"
  | "PRODUCT_NOT_FOUND"
  | "PRODUCT_NOT_MAPPED"
  | "DATE_REQUIRED"
  | "DATE_CLOSED"
  | "SOLD_OUT"
  | "DUPLICATE_RESERVATION"
  | "INVALID_RESERVATION"
  | "SPAM_BLOCKED"
  | "PERMISSION_DENIED"
  | "VALIDATION_ERROR"
  | "SERVER_ERROR";

export type UnoApiError = {
  code: UnoApiErrorCode;
  message: string;
  details?: unknown;
};

export type UnoApiSuccess<T> = {
  ok: true;
  data: T;
};

export type UnoApiFailure = {
  ok: false;
  error: UnoApiError;
};

export type UnoApiResponse<T> = UnoApiSuccess<T> | UnoApiFailure;

export type UnoApiRequestOptions = Omit<RequestInit, "body"> & {
  body?: unknown;
  query?: Record<string, string | number | boolean | null | undefined>;
};

export class UnoApiRequestError extends Error {
  status: number;
  response: UnoApiFailure;

  constructor(status: number, response: UnoApiFailure) {
    super(response.error.message);
    this.name = "UnoApiRequestError";
    this.status = status;
    this.response = response;
  }
}

const DEFAULT_API_BASE_URL = "/api";

export const getUnoApiBaseUrl = () => {
  const envBaseUrl = import.meta.env.VITE_UNOTRAVEL_API_BASE_URL;
  return typeof envBaseUrl === "string" && envBaseUrl.trim()
    ? envBaseUrl.trim().replace(/\/+$/, "")
    : DEFAULT_API_BASE_URL;
};

const createApiUrl = (
  path: string,
  query?: UnoApiRequestOptions["query"],
) => {
  const baseUrl = getUnoApiBaseUrl();
  const normalizedPath = path.startsWith("/") ? path : `/${path}`;
  const url = new URL(`${baseUrl}${normalizedPath}`, window.location.origin);

  Object.entries(query ?? {}).forEach(([key, value]) => {
    if (value === null || value === undefined || value === "") return;
    url.searchParams.set(key, String(value));
  });

  return url.toString();
};

const createServerErrorResponse = (message: string): UnoApiFailure => ({
  ok: false,
  error: {
    code: "SERVER_ERROR",
    message,
  },
});

const parseApiResponseText = <T>(text: string): UnoApiResponse<T> | null => {
  const trimmedText = text.trim();

  if (!trimmedText) return null;

  try {
    return JSON.parse(trimmedText) as UnoApiResponse<T>;
  } catch {
    const jsonStart = trimmedText.indexOf("{");
    const jsonEnd = trimmedText.lastIndexOf("}");

    if (jsonStart < 0 || jsonEnd <= jsonStart) return null;

    try {
      return JSON.parse(trimmedText.slice(jsonStart, jsonEnd + 1)) as UnoApiResponse<T>;
    } catch {
      return null;
    }
  }
};

export async function unoApiRequest<T>(
  path: string,
  options: UnoApiRequestOptions = {},
): Promise<UnoApiResponse<T>> {
  const { body, query, headers, ...requestInit } = options;
  const hasBody = body !== undefined;
  const method = requestInit.method;

  try {
    const response = await fetch(createApiUrl(path, query), {
      credentials: "include",
      ...requestInit,
      headers: {
        Accept: "application/json",
        ...(hasBody ? { "Content-Type": "application/json" } : {}),
        ...createApiSecurityHeaders(method),
        ...headers,
      },
      body: hasBody ? JSON.stringify(body) : undefined,
    });

    const responseText = await response.text();
    const payload = parseApiResponseText<T>(responseText);

    if (!payload || typeof payload !== "object" || !("ok" in payload)) {
      return createServerErrorResponse(
        response.status >= 500
          ? "서버에서 문의 저장 중 오류가 발생했습니다."
          : "API 응답 형식이 올바르지 않습니다.",
      );
    }

    return payload;
  } catch {
    return createServerErrorResponse("API 서버와 연결할 수 없습니다.");
  }
}

export async function unoApiData<T>(
  path: string,
  options: UnoApiRequestOptions = {},
): Promise<T> {
  const response = await unoApiRequest<T>(path, options);

  if (!response.ok) {
    throw new UnoApiRequestError(200, response);
  }

  return response.data;
}
