// ReservationPage.tsx
// 상품 상세의 예약하기 CTA 이후 진입하는 프런트 예약 진행 페이지다.
// 예약 안내/환불 규정 동의, 상품 타입별 예약 정보 입력, 예약 요약 상태를 관리한다.
// 마이페이지 예약목록과 달리 새 예약을 작성하는 역할만 맡아 경로와 기능이 겹치지 않도록 분리한다.

import { useMemo, useState } from "react";
import {
  DEFAULT_MY_CART_PAGE_URL,
  type ReservationStoragePayload,
  getPendingReservation,
  navigateInternal,
  saveSubmittedReservation,
} from "../product/product_com/reservationStore";

const FONT_EN = "var(--font-en)";
const FONT_KO = "var(--font-ko)";
const BLACK = "#151515";
const BORDER = "rgba(21, 21, 21, 0.14)";
const YELLOW = "#FCC800";

const STYLE = `
  .reservation-shell {
    width: 100%;
    min-width: 1024px;
    min-height: 100vh;
    padding: 160px 52px 96px;
    box-sizing: border-box;
    background:
      linear-gradient(90deg, rgba(21,21,21,0.045) 1px, transparent 1px) 0 0 / 124px 100%,
      #ffffff;
    color: ${BLACK};
  }

  .reservation-wrap {
    width: min(1580px, 100%);
    margin: 0 auto;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 460px;
    gap: 42px;
    align-items: start;
  }

  .reservation-hero {
    min-height: 250px;
    display: grid;
    grid-template-columns: minmax(0, 0.9fr) minmax(360px, 0.7fr);
    gap: 44px;
    align-items: end;
    padding-bottom: 54px;
    border-bottom: 1px solid ${BORDER};
  }

  .reservation-eyebrow,
  .reservation-step,
  .reservation-kicker,
  .reservation-policy-label,
  .reservation-table-label {
    font-family: ${FONT_EN};
    font-size: 11px;
    line-height: 1;
    letter-spacing: 0.18em;
    font-weight: 900;
    text-transform: uppercase;
    color: rgba(21, 21, 21, 0.46);
  }

  .reservation-title {
    margin: 24px 0 0;
    font-family: ${FONT_KO};
    font-size: clamp(72px, 7vw, 132px);
    line-height: 0.9;
    letter-spacing: -0.075em;
    font-weight: 900;
  }

  .reservation-lead {
    margin: 0;
    max-width: 560px;
    font-family: ${FONT_KO};
    font-size: 18px;
    line-height: 1.8;
    letter-spacing: -0.045em;
    font-weight: 560;
    color: rgba(21, 21, 21, 0.62);
    word-break: keep-all;
  }

  .reservation-panel,
  .reservation-summary,
  .reservation-policy-panel {
    background: rgba(255, 255, 255, 0.96);
    border: 1px solid ${BORDER};
    box-sizing: border-box;
  }

  .reservation-panel,
  .reservation-policy-panel {
    margin-top: 42px;
  }

  .reservation-policy-head {
    display: grid;
    grid-template-columns: 230px minmax(0, 1fr);
    gap: 34px;
    padding: 34px 36px;
    border-bottom: 1px solid ${BORDER};
  }

  .reservation-policy-head h2,
  .reservation-section h2 {
    margin: 12px 0 0;
    font-family: ${FONT_KO};
    font-size: 28px;
    line-height: 1.05;
    letter-spacing: -0.055em;
    font-weight: 840;
  }

  .reservation-policy-head p,
  .reservation-note {
    margin: 0 0 12px;
    font-family: ${FONT_KO};
    font-size: 16px;
    line-height: 1.75;
    letter-spacing: -0.04em;
    color: rgba(21, 21, 21, 0.64);
    word-break: keep-all;
  }

  .reservation-policy-box {
    max-height: 430px;
    overflow: auto;
    padding: 34px 36px 38px;
    border-bottom: 1px solid ${BORDER};
    font-family: ${FONT_KO};
    font-size: 15px;
    line-height: 1.82;
    letter-spacing: -0.036em;
    color: rgba(21, 21, 21, 0.74);
    word-break: keep-all;
  }

  .reservation-policy-box h3 {
    margin: 0 0 16px;
    font-family: ${FONT_KO};
    font-size: 22px;
    line-height: 1.1;
    letter-spacing: -0.05em;
    color: ${BLACK};
  }

  .reservation-policy-box h4 {
    margin: 30px 0 10px;
    font-family: ${FONT_KO};
    font-size: 17px;
    line-height: 1.2;
    letter-spacing: -0.04em;
    color: ${BLACK};
  }

  .reservation-policy-box p {
    margin: 0 0 14px;
  }

  .reservation-policy-check {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    padding: 24px 36px;
  }

  .reservation-check,
  .reservation-agree {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    font-family: ${FONT_KO};
    font-size: 15px;
    line-height: 1.7;
    letter-spacing: -0.04em;
    color: rgba(21, 21, 21, 0.72);
    word-break: keep-all;
  }

  .reservation-check input,
  .reservation-agree input {
    width: 18px;
    height: 18px;
    margin-top: 4px;
    accent-color: ${BLACK};
  }

  .reservation-section {
    display: grid;
    grid-template-columns: 210px minmax(0, 1fr);
    gap: 28px;
    padding: 34px 36px;
    border-bottom: 1px solid ${BORDER};
  }

  .reservation-section:last-child {
    border-bottom: 0;
  }

  .reservation-fields {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
  }

  .reservation-fields.is-four {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }

  .reservation-field {
    display: flex;
    flex-direction: column;
    gap: 9px;
  }

  .reservation-field.is-wide {
    grid-column: 1 / -1;
  }

  .reservation-field span,
  .reservation-agree span {
    font-family: ${FONT_KO};
    font-size: 13px;
    line-height: 1;
    letter-spacing: -0.035em;
    font-weight: 760;
    color: rgba(21, 21, 21, 0.62);
  }

  .reservation-field input,
  .reservation-field textarea,
  .reservation-field select {
    width: 100%;
    border: 0;
    border-bottom: 1px solid rgba(21, 21, 21, 0.22);
    border-radius: 0;
    padding: 0 0 12px;
    background: transparent;
    color: ${BLACK};
    font-family: ${FONT_KO};
    font-size: 17px;
    line-height: 1.4;
    letter-spacing: -0.04em;
    outline: none;
    box-sizing: border-box;
  }

  .reservation-field textarea {
    min-height: 96px;
    resize: vertical;
  }

  .reservation-field input:focus,
  .reservation-field textarea:focus,
  .reservation-field select:focus {
    border-bottom-color: ${BLACK};
  }

  .reservation-tour-table {
    width: 100%;
    border-collapse: collapse;
    font-family: ${FONT_KO};
    font-size: 15px;
    line-height: 1.55;
    letter-spacing: -0.04em;
  }

  .reservation-tour-table th {
    padding: 0 0 14px;
    border-bottom: 1px solid rgba(21, 21, 21, 0.22);
    color: rgba(21, 21, 21, 0.48);
    font-weight: 800;
    text-align: left;
    white-space: nowrap;
  }

  .reservation-tour-table td {
    padding: 18px 16px 18px 0;
    border-bottom: 1px solid ${BORDER};
    vertical-align: top;
  }

  .reservation-tour-table strong {
    font-weight: 820;
    color: ${BLACK};
  }

  .reservation-option {
    display: block;
    margin-top: 8px;
    color: rgba(21, 21, 21, 0.58);
  }

  .reservation-payment-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    border-top: 1px solid ${BORDER};
    margin-top: 22px;
  }

  .reservation-payment-grid div {
    padding: 18px 20px 0 0;
    font-family: ${FONT_KO};
    font-size: 15px;
    line-height: 1.5;
    letter-spacing: -0.04em;
  }

  .reservation-payment-grid span {
    display: block;
    margin-bottom: 8px;
    color: rgba(21, 21, 21, 0.48);
    font-weight: 800;
  }

  .reservation-payment-grid strong {
    font-family: ${FONT_EN};
    font-size: 28px;
    line-height: 1;
    letter-spacing: -0.04em;
    font-weight: 900;
  }

  .reservation-complete-panel {
    margin-top: 42px;
    padding: 0;
    border: 1px solid ${BORDER};
    background: rgba(255, 255, 255, 0.96);
  }

  .reservation-complete-head,
  .reservation-complete-notice {
    padding: 34px 36px;
    border-bottom: 1px solid ${BORDER};
  }

  .reservation-complete-head h2,
  .reservation-complete-notice h2 {
    margin: 0;
    font-family: ${FONT_KO};
    font-size: 30px;
    line-height: 1.2;
    letter-spacing: -0.055em;
    font-weight: 480;
  }

  .reservation-complete-list {
    padding: 34px 36px;
    border-bottom: 1px solid ${BORDER};
  }

  .reservation-complete-price {
    width: 100%;
    border-collapse: collapse;
    margin-top: 24px;
    font-family: ${FONT_KO};
    font-size: 15px;
    line-height: 1.5;
    letter-spacing: -0.04em;
  }

  .reservation-complete-price td {
    padding: 14px 0;
    border-top: 1px solid ${BORDER};
  }

  .reservation-complete-price td:last-child {
    text-align: right;
  }

  .reservation-complete-price big {
    font-family: ${FONT_EN};
    font-size: 28px;
    line-height: 1;
    letter-spacing: -0.04em;
    font-weight: 900;
  }

  .reservation-complete-board {
    padding: 0 36px 34px;
    border-bottom: 1px solid ${BORDER};
  }

  .reservation-complete-board table {
    width: 100%;
    border-collapse: collapse;
    font-family: ${FONT_KO};
    font-size: 15px;
    line-height: 1.6;
    letter-spacing: -0.04em;
  }

  .reservation-complete-board th,
  .reservation-complete-board td {
    padding: 18px 0;
    border-bottom: 1px solid ${BORDER};
    text-align: left;
  }

  .reservation-complete-board th {
    width: 150px;
    color: rgba(21, 21, 21, 0.52);
    font-weight: 820;
  }

  .reservation-alert {
    color: #d24b2a;
    font-weight: 800;
  }

  .reservation-complete-notice h2 {
    margin-bottom: 20px;
    font-weight: 840;
  }

  .reservation-complete-notice p {
    margin: 0 0 10px;
    font-family: ${FONT_KO};
    font-size: 15px;
    line-height: 1.7;
    letter-spacing: -0.04em;
    color: rgba(21, 21, 21, 0.68);
    word-break: keep-all;
  }

  .reservation-complete-notice strong {
    color: #d24b2a;
  }

  .reservation-summary {
    position: sticky;
    top: 132px;
    padding: 30px;
  }

  .reservation-summary h2 {
    margin: 16px 0 24px;
    font-family: ${FONT_KO};
    font-size: 34px;
    line-height: 1.02;
    letter-spacing: -0.06em;
    font-weight: 860;
    word-break: keep-all;
  }

  .reservation-summary-list {
    display: grid;
    border-top: 1px solid ${BORDER};
  }

  .reservation-summary-row {
    display: grid;
    grid-template-columns: 132px minmax(0, 1fr);
    gap: 18px;
    padding: 17px 0;
    border-bottom: 1px solid ${BORDER};
    font-family: ${FONT_KO};
    font-size: 15px;
    line-height: 1.5;
    letter-spacing: -0.04em;
  }

  .reservation-summary-row span {
    color: rgba(21, 21, 21, 0.48);
    font-weight: 760;
  }

  .reservation-summary-row strong {
    font-weight: 760;
    color: ${BLACK};
    word-break: keep-all;
  }

  .reservation-total {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 22px;
    padding: 28px 0 26px;
    border-bottom: 1px solid ${BORDER};
  }

  .reservation-total span {
    font-family: ${FONT_KO};
    font-size: 14px;
    letter-spacing: -0.04em;
    font-weight: 760;
    color: rgba(21, 21, 21, 0.52);
  }

  .reservation-total strong {
    font-family: ${FONT_EN};
    font-size: 46px;
    line-height: 0.9;
    letter-spacing: -0.06em;
    font-weight: 920;
  }

  .reservation-actions {
    display: grid;
    gap: 10px;
    margin-top: 24px;
  }

  .reservation-button {
    appearance: none;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 58px;
    border: 1px solid ${BLACK};
    background: ${BLACK};
    color: #ffffff;
    cursor: pointer;
    font-family: ${FONT_KO};
    font-size: 15px;
    line-height: 1;
    letter-spacing: -0.035em;
    font-weight: 820;
    text-decoration: none;
    box-sizing: border-box;
  }

  .reservation-button.is-inline {
    width: auto;
    min-width: 180px;
    padding: 0 28px;
  }

  .reservation-button.is-subtle {
    background: #ffffff;
    color: ${BLACK};
    border-color: rgba(21, 21, 21, 0.18);
  }

  .reservation-button.is-yellow {
    background: ${YELLOW};
    color: ${BLACK};
    border-color: ${YELLOW};
  }

  .reservation-button:disabled {
    cursor: not-allowed;
    opacity: 0.34;
  }

  .reservation-message {
    margin: 18px 0 0;
    font-family: ${FONT_KO};
    font-size: 14px;
    line-height: 1.65;
    letter-spacing: -0.04em;
    color: rgba(21, 21, 21, 0.62);
    word-break: keep-all;
  }

  .reservation-empty {
    width: min(920px, 100%);
    margin: 0 auto;
    padding: 130px 54px;
    border: 1px solid ${BORDER};
    text-align: center;
    box-sizing: border-box;
    background: #fff;
  }

  .reservation-empty h1 {
    margin: 18px 0 18px;
    font-family: ${FONT_KO};
    font-size: 58px;
    line-height: 1;
    letter-spacing: -0.065em;
  }

  .reservation-empty p {
    margin: 0 auto 32px;
    max-width: 460px;
    font-family: ${FONT_KO};
    font-size: 16px;
    line-height: 1.7;
    letter-spacing: -0.04em;
    color: rgba(21, 21, 21, 0.58);
    word-break: keep-all;
  }
`;

const formatPrice = (value: number, currency = "KRW") => {
  const safeValue = Number.isFinite(value) ? value : 0;

  if (currency === "KRW") {
    return `${safeValue.toLocaleString("ko-KR")}원`;
  }

  return new Intl.NumberFormat("ko-KR", {
    style: "currency",
    currency,
    maximumFractionDigits: 0,
  }).format(safeValue);
};

const getProductTypeLabel = (type: ReservationStoragePayload["productType"]) =>
  type === "daily" ? "데일리 투어" : "세미 패키지";

const getLocalPaymentLabel = (reservation: ReservationStoragePayload) =>
  reservation.productType === "daily" ? "50유로" : "예약 확정 시 안내";

function ReservationSummary({
  reservation,
}: {
  reservation: ReservationStoragePayload;
}) {
  const depositLabel = formatPrice(reservation.totalPrice, reservation.currency);
  const localPaymentLabel = getLocalPaymentLabel(reservation);

  return (
    <aside className="reservation-summary" aria-label="예약 요약">
      <div className="reservation-kicker">BOOKING SUMMARY</div>
      <h2>{reservation.title}</h2>
      <div className="reservation-summary-list">
        <div className="reservation-summary-row">
          <span>상품 구분</span>
          <strong>{getProductTypeLabel(reservation.productType)}</strong>
        </div>
        <div className="reservation-summary-row">
          <span>투어일</span>
          <strong>
            {reservation.selectedDateLabel || reservation.selectedDateId}
            {reservation.selectedWeekday ? ` · ${reservation.selectedWeekday}` : ""}
          </strong>
        </div>
        <div className="reservation-summary-row">
          <span>예약 인원</span>
          <strong>{reservation.personCount}명</strong>
        </div>
        <div className="reservation-summary-row">
          <span>예약금</span>
          <strong>{depositLabel}</strong>
        </div>
        <div className="reservation-summary-row">
          <span>현지지불금</span>
          <strong>{localPaymentLabel}</strong>
        </div>
      </div>
      <div className="reservation-total">
        <span>총 투어 금액</span>
        <strong>{depositLabel}</strong>
      </div>
    </aside>
  );
}

function TourProductInfo({
  reservation,
}: {
  reservation: ReservationStoragePayload;
}) {
  const depositLabel = formatPrice(reservation.totalPrice, reservation.currency);
  const localPaymentLabel = getLocalPaymentLabel(reservation);

  return (
    <div>
      <table className="reservation-tour-table">
        <thead>
          <tr>
            <th>투어상품정보</th>
            <th>인원</th>
            <th>투어일</th>
            <th>예약금</th>
            <th>현지지불금</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <strong>{reservation.title}</strong>
              <span className="reservation-option">
                {reservation.productType === "daily"
                  ? "만 6세 이상 ~ 성인"
                  : "세미패키지 기본 예약"}
              </span>
            </td>
            <td>{reservation.personCount}명</td>
            <td>{reservation.selectedDateLabel || reservation.selectedDateId}</td>
            <td>{depositLabel}</td>
            <td>{localPaymentLabel}</td>
          </tr>
        </tbody>
      </table>
      <div className="reservation-payment-grid">
        <div>
          <span>총 투어 금액</span>
          <strong>{depositLabel}</strong>
        </div>
        <div>
          <span>현지지불금</span>
          <strong>{localPaymentLabel}</strong>
        </div>
      </div>
    </div>
  );
}

function ReservationComplete({
  reservation,
}: {
  reservation: ReservationStoragePayload;
}) {
  const depositLabel = formatPrice(reservation.totalPrice, reservation.currency);
  const localPaymentLabel = getLocalPaymentLabel(reservation);
  const depositDue = new Date(Date.now() + 12 * 60 * 60 * 1000);
  const dueLabel = new Intl.DateTimeFormat("ko-KR", {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
    hour: "2-digit",
    hour12: false,
  }).format(depositDue);

  return (
    <main className="reservation-shell">
      <style>{STYLE}</style>
      <div className="reservation-wrap">
        <div>
          <section className="reservation-hero" aria-label="예약 신청 완료">
            <div>
              <div className="reservation-eyebrow">UNO TRAVEL / COMPLETE</div>
              <h1 className="reservation-title">예약 신청</h1>
            </div>
            <p className="reservation-lead">
              다음과 같이 투어 예약이 신청되었습니다. 예약금 결제 또는 입금이 확인되면
              예약 확정 단계로 진행됩니다.
            </p>
          </section>

          <section className="reservation-complete-panel" aria-label="예약 신청 완료">
            <div className="reservation-complete-head">
              <h2>다음과 같이 투어 예약이 신청되었습니다.</h2>
            </div>

            <div className="reservation-complete-list">
              <TourProductInfo reservation={reservation} />
              <table className="reservation-complete-price">
                <tbody>
                  <tr>
                    <td>예약금</td>
                    <td>
                      <big>{depositLabel.replace("원", "")}</big>원
                    </td>
                  </tr>
                  <tr>
                    <td>현지지불금</td>
                    <td>
                      <big>{localPaymentLabel.replace("유로", "")}</big>
                      {localPaymentLabel.includes("유로") ? "유로" : ""}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div className="reservation-complete-board">
              <table>
                <tbody>
                  <tr>
                    <th>예약금</th>
                    <td>
                      예약금 {depositLabel}{" "}
                      <span className="reservation-alert">({dueLabel}시까지)</span>
                    </td>
                  </tr>
                  <tr>
                    <th>현장지불금</th>
                    <td>{localPaymentLabel}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div className="reservation-complete-notice">
              <h2>예약금 입금 유의사항</h2>
              <p>
                ※ 예약 신청 후 진행상태가 <strong>예약 확인 상태</strong>라면,
                12시간 이내에 예약금 결제를 완료해야 합니다. 미결제 시 예약이
                취소됩니다.
              </p>
              <p>
                ※ 예약금 카드결제를 원하는 경우 하단의 마이페이지 버튼을 눌러
                예약목록에서 결제를 진행하면 됩니다.
              </p>
              <p>※ 예약 확정 시 바우처가 이메일로 발송되며, 별도로 출력할 수 있습니다.</p>
              <p>※ 예약금 결제자 순으로 예약이 확정됩니다.</p>
              <p>
                ※ 예약금 없이 현장에서 투어비용 전액을 지불하기 원하신다면 별도의
                연락을 주셔야 합니다.
              </p>
              <p>
                ※ 이탈리아 전용 입금 계좌 : 신한은행 140-010-791268
                (예금주: (주)우노컴패니)
              </p>
              <p>
                ※ 세미패키지 전용 입금 계좌 : 우리은행 1005-302-870059
                (예금주: (주)우노컴패니)
              </p>
            </div>
          </section>
        </div>

        <div>
          <ReservationSummary reservation={reservation} />
          <div className="reservation-actions">
            <a
              href="/mypage/reservations"
              className="reservation-button is-yellow"
              onClick={(event) => {
                event.preventDefault();
                navigateInternal("/mypage/reservations");
              }}
            >
              마이페이지
            </a>
            <a
              href={reservation.href}
              className="reservation-button is-subtle"
              onClick={(event) => {
                event.preventDefault();
                navigateInternal(reservation.href);
              }}
            >
              상품 다시 보기
            </a>
          </div>
        </div>
      </div>
    </main>
  );
}

function PolicyContent() {
  return (
    <div className="reservation-policy-box" tabIndex={0}>
      <h3>투어 환불 규정</h3>
      <h4>[단체 투어]</h4>
      <p>
        여행자는 여행자 약관 제13조에 따라 여행요금 지급이 이루어진 후 투어
        개시일 이전에 여행계약을 임의로 해제하는 경우, 해제 통보 시점에 관한
        기준에 따라 여행요금이 환불됩니다.
      </p>
      <p>
        투어개시일로부터 30일까지 통보 시 여행요금 전액 환불, 29일부터 20일 전
        통보 시 부가서비스를 제외한 투어요금의 30% 배상, 19일부터 2일 전 통보 시
        50% 배상, 투어개시일 1일 전부터 당일 통보 시 100% 배상이 적용됩니다.
      </p>
      <p>
        사전 예약 입장권, 호텔 등은 예약 확정과 동시에 구매 또는 결제가
        이루어지기 때문에 국외여행 표준약관이 아닌 특별약관이 적용됩니다.
      </p>
      <h4>[세미패키지 투어]</h4>
      <p>
        본 상품은 항공사, 현지 호텔, 티켓 등 여행 일정 전반이 여행자의 투어
        확정과 동시에 예약 진행되는 상품으로 국외여행 표준약관이 아닌 특별약관이
        적용됩니다.
      </p>
      <p>
        투어예약 후 여행개시 60일 전 취소 시 위약금 40만원을 제외하고 환불됩니다.
        여행개시 60일부터 30일 전 취소 시 위약금 80만원과 항공 취소 수수료,
        30일부터 15일 전 취소 시 위약금 100만원과 항공 취소 수수료가 차감됩니다.
      </p>
      <p>
        여행개시 15일부터 10일 전 취소 시 총 투어비의 40%, 10일부터 2일 전 취소
        시 총 투어비의 80%, 2일 전부터 당일 취소 시 총 여행경비 100%가 차감됩니다.
      </p>
      <h4>[최소 출발 인원]</h4>
      <p>
        본 상품은 최소 출발인원 8명 이상 모객 시 출발 가능합니다. 최저행사인원이
        충족되지 않아 여행계약을 해지할 경우 여행개시 7일 전까지 여행자에게
        통지해야 합니다.
      </p>
      <h4>[항공 및 호텔 예약]</h4>
      <p>
        세미패키지는 예약 확정 후 항공권과 호텔 수배가 진행됩니다. 객실 형태와
        항공권 취소는 현지 및 항공사 규정에 따라 별도 비용이 발생할 수 있습니다.
      </p>
    </div>
  );
}

export default function ReservationPage() {
  const reservation = useMemo(() => getPendingReservation(), []);
  const [policyAgreed, setPolicyAgreed] = useState(false);
  const [isPolicyAccepted, setIsPolicyAccepted] = useState(false);
  const [name, setName] = useState("");
  const [phone, setPhone] = useState("");
  const [email, setEmail] = useState("");
  const [kakaoId, setKakaoId] = useState("");
  const [memo, setMemo] = useState("");
  const [passportName, setPassportName] = useState("");
  const [passportNumber, setPassportNumber] = useState("");
  const [passportBirth, setPassportBirth] = useState("");
  const [passportExpiry, setPassportExpiry] = useState("");
  const [roomType, setRoomType] = useState("twin");
  const [roomRequest, setRoomRequest] = useState("");
  const [finalAgreed, setFinalAgreed] = useState(false);
  const [isSubmitted, setIsSubmitted] = useState(false);
  const [message, setMessage] = useState("");

  const isSemiPackage = reservation?.productType === "semi";
  const canSubmit = Boolean(
    reservation &&
      name.trim() &&
      phone.trim() &&
      finalAgreed &&
      (!isSemiPackage ||
        (passportName.trim() && passportNumber.trim() && passportExpiry.trim())),
  );

  const handleSubmit = () => {
    if (!reservation) return;

    if (!canSubmit) {
      setMessage("필수 정보를 확인해 주세요.");
      return;
    }

    const reservationDetails = {
      customer: {
        name: name.trim(),
        phone: phone.trim(),
        email: email.trim(),
        kakaoId: kakaoId.trim(),
        memo: memo.trim(),
      },
      passport: isSemiPackage
        ? {
            name: passportName.trim(),
            number: passportNumber.trim(),
            birth: passportBirth.trim(),
            expiry: passportExpiry.trim(),
          }
        : null,
      room: isSemiPackage
        ? {
            type: roomType,
            request: roomRequest.trim(),
          }
        : null,
    };

    window.sessionStorage.setItem(
      "unotravel_reservation_draft",
      JSON.stringify({
        ...reservation,
        ...reservationDetails,
        policyAgreedAt: Date.now(),
        submittedAt: Date.now(),
      }),
    );
    saveSubmittedReservation(reservation, reservationDetails);
    setIsSubmitted(true);
    setMessage(
      "예약 신청 정보가 저장되었습니다. 실제 결제와 확정은 백엔드 연결 후 이어집니다.",
    );
  };

  if (!reservation) {
    return (
      <main className="reservation-shell">
        <style>{STYLE}</style>
        <section className="reservation-empty" aria-label="예약 정보 없음">
          <div className="reservation-eyebrow">RESERVATION</div>
          <h1>예약 정보가 없습니다</h1>
          <p>
            상품 상세에서 날짜와 인원을 먼저 선택하면 예약 진행 페이지에서 신청
            정보를 이어서 작성할 수 있습니다.
          </p>
          <button
            type="button"
            className="reservation-button is-yellow"
            onClick={() => navigateInternal("/product/semi/italy")}
          >
            상품 보러가기
          </button>
        </section>
      </main>
    );
  }

  if (isSubmitted) {
    return <ReservationComplete reservation={reservation} />;
  }

  if (!isPolicyAccepted) {
    return (
      <main className="reservation-shell">
        <style>{STYLE}</style>
        <div className="reservation-wrap">
          <div>
            <section className="reservation-hero" aria-label="예약 안내">
              <div>
                <div className="reservation-eyebrow">UNO TRAVEL / AGREEMENT</div>
                <h1 className="reservation-title">예약 안내</h1>
              </div>
              <p className="reservation-lead">
                투어마다 환불 규정이 다르게 적용되니, 예약 전 반드시 해당 투어의
                환불 및 취소 규정을 확인하시기 바랍니다.
              </p>
            </section>

            <section className="reservation-policy-panel" aria-label="환불 규정 동의">
              <div className="reservation-policy-head">
                <div>
                  <div className="reservation-policy-label">NOTICE</div>
                  <h2>예약 전 확인</h2>
                </div>
                <div>
                  <p>
                    우노트래블의 모든 투어는 예약금 입금 순서대로 확정 처리됩니다.
                  </p>
                  <p>
                    예약금이 입금되지 않은 투어는 2일 후 자동으로 취소 처리되고
                    있습니다.
                  </p>
                </div>
              </div>

              <PolicyContent />

              <div className="reservation-policy-check">
                <label className="reservation-check">
                  <input
                    type="checkbox"
                    checked={policyAgreed}
                    onChange={(event) => setPolicyAgreed(event.target.checked)}
                  />
                  <span>투어 환불/취소 규정에 동의합니다.</span>
                </label>
                <button
                  type="button"
                  className="reservation-button is-yellow is-inline"
                  disabled={!policyAgreed}
                  onClick={() => setIsPolicyAccepted(true)}
                >
                  예약 정보 입력
                </button>
              </div>
            </section>
          </div>

          <ReservationSummary reservation={reservation} />
        </div>
      </main>
    );
  }

  return (
    <main className="reservation-shell">
      <style>{STYLE}</style>
      <div className="reservation-wrap">
        <div>
          <section className="reservation-hero" aria-label="예약 진행">
            <div>
              <div className="reservation-eyebrow">UNO TRAVEL / RESERVATION</div>
              <h1 className="reservation-title">예약 진행</h1>
            </div>
            <p className="reservation-lead">
              {isSemiPackage
                ? "세미패키지는 여권 정보와 룸 타입 정보를 함께 확인합니다."
                : "데일리투어는 투어 상품 정보, 신청자 정보, 기타사항만 간단히 입력합니다."}
            </p>
          </section>

          <section className="reservation-panel" aria-label="예약 정보 입력">
            <div className="reservation-section">
              <div>
                <div className="reservation-step">01 / TOUR</div>
                <h2>투어 상품 정보</h2>
              </div>
              <TourProductInfo reservation={reservation} />
            </div>

            <div className="reservation-section">
              <div>
                <div className="reservation-step">02 / GUEST</div>
                <h2>투어 신청자 정보</h2>
              </div>
              <div>
                <p className="reservation-note">
                  해당 정보의 기입 오기로 인해 발생하는 불이익에 대해서는 당사에서
                  도움을 드릴 수 없습니다.
                </p>
                <p className="reservation-note">
                  네이버, 카카오톡, 구글 계정으로 로그인한 경우 필히 신청자 정보를
                  작성해 주세요.
                </p>
                <div className="reservation-fields is-four">
                  <label className="reservation-field">
                    <span>이름</span>
                    <input
                      value={name}
                      onChange={(event) => setName(event.target.value)}
                      placeholder="홍길동"
                    />
                  </label>
                  <label className="reservation-field">
                    <span>연락처</span>
                    <input
                      value={phone}
                      onChange={(event) => setPhone(event.target.value)}
                      placeholder="010-0000-0000"
                    />
                  </label>
                  <label className="reservation-field">
                    <span>이메일</span>
                    <input
                      value={email}
                      onChange={(event) => setEmail(event.target.value)}
                      placeholder="mail@example.com"
                    />
                  </label>
                  <label className="reservation-field">
                    <span>카카오톡 ID</span>
                    <input
                      value={kakaoId}
                      onChange={(event) => setKakaoId(event.target.value)}
                      placeholder="카카오톡 ID"
                    />
                  </label>
                </div>
              </div>
            </div>

            {isSemiPackage ? (
              <>
                <div className="reservation-section">
                  <div>
                    <div className="reservation-step">03 / PASSPORT</div>
                    <h2>여권 정보</h2>
                  </div>
                  <div className="reservation-fields">
                    <label className="reservation-field">
                      <span>영문 성명</span>
                      <input
                        value={passportName}
                        onChange={(event) => setPassportName(event.target.value)}
                        placeholder="HONG GILDONG"
                      />
                    </label>
                    <label className="reservation-field">
                      <span>여권 번호</span>
                      <input
                        value={passportNumber}
                        onChange={(event) => setPassportNumber(event.target.value)}
                        placeholder="M12345678"
                      />
                    </label>
                    <label className="reservation-field">
                      <span>생년월일</span>
                      <input
                        value={passportBirth}
                        onChange={(event) => setPassportBirth(event.target.value)}
                        placeholder="YYYY-MM-DD"
                      />
                    </label>
                    <label className="reservation-field">
                      <span>여권 만료일</span>
                      <input
                        value={passportExpiry}
                        onChange={(event) => setPassportExpiry(event.target.value)}
                        placeholder="YYYY-MM-DD"
                      />
                    </label>
                  </div>
                </div>

                <div className="reservation-section">
                  <div>
                    <div className="reservation-step">04 / ROOM</div>
                    <h2>룸 타입 정보</h2>
                  </div>
                  <div className="reservation-fields">
                    <label className="reservation-field">
                      <span>룸 타입</span>
                      <select
                        value={roomType}
                        onChange={(event) => setRoomType(event.target.value)}
                      >
                        <option value="twin">2인 1실 트윈룸</option>
                        <option value="double">더블룸 요청</option>
                        <option value="single">1인실 요청</option>
                        <option value="triple">3인실 요청</option>
                      </select>
                    </label>
                    <label className="reservation-field is-wide">
                      <span>룸 요청사항</span>
                      <textarea
                        value={roomRequest}
                        onChange={(event) => setRoomRequest(event.target.value)}
                        placeholder="동행자 성별, 룸메이트 요청, 1인실 요청 등 객실 관련 내용을 입력하세요."
                      />
                    </label>
                  </div>
                </div>
              </>
            ) : null}

            <div className="reservation-section">
              <div>
                <div className="reservation-step">
                  {isSemiPackage ? "05 / REQUEST" : "03 / REQUEST"}
                </div>
                <h2>기타사항</h2>
              </div>
              <div>
                <p className="reservation-note">
                  투어 신청비용 선 결제자 순으로 예약확정 됩니다.
                </p>
                <p className="reservation-note">
                  선 결제 없이 당일 전액 현지에서 현장지불하길 원하신다면 별도의
                  연락을 주셔야 합니다.
                </p>
                <label className="reservation-field is-wide">
                  <span>기타 사항</span>
                  <textarea
                    value={memo}
                    onChange={(event) => setMemo(event.target.value)}
                    placeholder="기타 사항을 입력하세요."
                  />
                </label>
              </div>
            </div>

            <div className="reservation-section">
              <div>
                <div className="reservation-step">
                  {isSemiPackage ? "06 / AGREEMENT" : "04 / AGREEMENT"}
                </div>
                <h2>최종 확인</h2>
              </div>
              <label className="reservation-agree">
                <input
                  type="checkbox"
                  checked={finalAgreed}
                  onChange={(event) => setFinalAgreed(event.target.checked)}
                />
                <span>
                  위 예약 정보와 환불/취소 규정을 확인했으며 예약 진행에
                  동의합니다.
                </span>
              </label>
            </div>
          </section>
        </div>

        <div>
          <ReservationSummary reservation={reservation} />
          <div className="reservation-actions">
            <button
              type="button"
              className="reservation-button is-yellow"
              disabled={!canSubmit}
              onClick={handleSubmit}
            >
              예약 신청하기
            </button>
            <button
              type="button"
              className="reservation-button is-subtle"
              onClick={() => setIsPolicyAccepted(false)}
            >
              환불 규정 다시 보기
            </button>
            <button
              type="button"
              className="reservation-button is-subtle"
              onClick={() => navigateInternal(reservation.href)}
            >
              상품 다시 보기
            </button>
            <button
              type="button"
              className="reservation-button is-subtle"
              onClick={() => navigateInternal(DEFAULT_MY_CART_PAGE_URL)}
            >
              장바구니 보기
            </button>
          </div>
          {message ? <p className="reservation-message">{message}</p> : null}
        </div>
      </div>
    </main>
  );
}
