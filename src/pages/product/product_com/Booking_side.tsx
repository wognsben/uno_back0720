// Booking_side.tsx
// 상품 상세 본문과 하단에서 노출되는 fixed 플로팅 예약 도크와 선택 패널을 담당한다.
// 스크롤 compact 상태, 날짜/인원 패널, blur backdrop, 도트 그리드 메뉴, CTA 상태를 관리한다.
// 히어로 아래 ReservationModule과 저장 로직이 충돌하지 않도록 reservationStore의 공통 함수만 사용한다.

import { useEffect, useRef, useMemo, useState } from "react";
import { createPortal } from "react-dom";

import {
  PriceText,
  type AvailableDate,
  getAvailabilityDisplayLabel,
  getAvailabilityStatus,
  isDateSoldOut,
} from "./reservationUtils";
import {
  DEFAULT_MY_CART_PAGE_URL,
  DEFAULT_RESERVATION_PAGE_URL,
  createReservationPayload,
  isReservationUserLoggedIn,
  navigateInternal,
  navigateToLoginForReservation,
  saveCartReservation,
  savePendingReservation,
} from "./reservationStore";

type ProductKind = "semi" | "daily";

type BookingSideProduct = {
  id: string;
  legacyProductId?: number | string;
  title: string;
  productType: ProductKind;
  basePrice: number;
  currency?: string;
  duration?: string;
  routeCode?: string;
};

type BookingSideProps = {
  product: BookingSideProduct;
  availableDates: AvailableDate[];
  kakaoChannelUrl?: string;
  cartHref?: string;
  reservationHref?: string;
};

const DEFAULT_KAKAO_CHANNEL_URL = "https://pf.kakao.com/_YOUR_CHANNEL_ID/chat";
const STYLE_ID = "uno-booking-side-style";

function formatCompactDateLabel(value: string) {
  const match = value.match(/^(\d{4})[.-](\d{2})[.-](\d{2})$/);
  if (!match) return value;
  return `${Number(match[2])}.${Number(match[3])}`;
}


function normalizeDateKey(value: string) {
  return value.replaceAll(".", "-");
}

function getBookingSideStyle() {
  return `
  /* ─── Floating Toolbar ─────────────────────────────── */

  .uno-booking-toolbar {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    z-index: 890;
    background: #f2efe7;
    border: 0;
    box-shadow: 0 -18px 54px rgba(21, 21, 21, 0.06);
    box-sizing: border-box;
    transition:
      width 0.72s cubic-bezier(0.075, 0.82, 0.165, 1),
      left 0.72s cubic-bezier(0.075, 0.82, 0.165, 1),
      bottom 0.72s cubic-bezier(0.075, 0.82, 0.165, 1),
      border-radius 0.72s cubic-bezier(0.075, 0.82, 0.165, 1),
      transform 0.72s cubic-bezier(0.075, 0.82, 0.165, 1),
      box-shadow 0.45s ease;
  }

  .uno-booking-toolbar__inner {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: 190px minmax(170px, 1fr) auto 64px;
    align-items: center;
    gap: 0;
    max-width: 1600px;
    margin: 0 auto;
    min-height: 92px;
    padding: 0 50px;
    box-sizing: border-box;
    transition:
      min-height 0.72s cubic-bezier(0.075, 0.82, 0.165, 1),
      padding 0.72s cubic-bezier(0.075, 0.82, 0.165, 1);
  }

  .uno-booking-toolbar.is-compact {
    left: 50%;
    bottom: 22px;
    width: min(520px, calc(100% - 40px));
    border-radius: 28px 28px 34px 34px / 24px 24px 32px 32px;
    transform: translateX(-50%);
    background: #171714;
    box-shadow:
      14px 14px 34px rgba(21, 21, 21, 0.14),
      -10px -10px 28px rgba(255, 255, 255, 0.82);
    overflow: visible;
  }

  .uno-booking-toolbar.is-compact::before {
    content: "";
    position: absolute;
    z-index: 0;
    top: -10px;
    left: 50%;
    width: 124px;
    height: 20px;
    border-radius: 0 0 62px 62px;
    background: #ffffff;
    transform: translateX(-50%);
    pointer-events: none;
  }

  .uno-booking-toolbar.is-compact .uno-booking-toolbar__inner {
    grid-template-columns: auto auto 58px;
    min-height: 76px;
    padding: 0 10px 0 24px;
  }

  .uno-booking-toolbar.is-compact .uno-booking-toolbar__toggle,
  .uno-booking-toolbar.is-compact .uno-booking-toolbar__summary,
  .uno-booking-toolbar.is-compact .uno-booking-toolbar__btn.is-cart,
  .uno-booking-toolbar.is-compact .uno-booking-toolbar__btn.is-kakao {
    display: none;
  }

  .uno-booking-toolbar.is-compact .uno-booking-toolbar__price,
  .uno-booking-toolbar.is-compact .uno-booking-toolbar__price-label {
    color: #ffffff;
  }

  .uno-booking-toolbar.is-compact .uno-booking-toolbar__price-label {
    opacity: 0.48;
  }

  .uno-booking-toolbar.is-compact .uno-booking-toolbar__btn.is-primary {
    background: #f2efe7;
    color: #151515;
  }

  .uno-booking-toolbar.is-compact .uno-booking-toolbar__dock-toggle {
    background: #24241f;
    color: #ffffff;
    box-shadow:
      7px 7px 16px rgba(0, 0, 0, 0.42),
      -5px -5px 13px rgba(255, 255, 255, 0.08);
  }

  /* 왼쪽: 날짜·인원 선택 토글 */
  .uno-booking-toolbar__toggle {
    appearance: none;
    border: 0;
    border-radius: 0;
    background: #f2efe7;
    padding: 0 24px;
    height: 92px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-family: var(--font-ko);
    font-size: 13px;
    font-weight: 700;
    letter-spacing: -0.035em;
    color: #151515;
    white-space: nowrap;
    flex-shrink: 0;
    transition: background 0.2s ease, border-color 0.2s ease;
  }

  .uno-booking-toolbar__toggle:hover {
    background: #f4f4f1;
  }

  .uno-booking-toolbar__toggle.is-open {
    background: #151515;
    border-color: #151515;
    color: #ffffff;
  }

  .uno-booking-toolbar__toggle-arrow {
    font-size: 11px;
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    display: inline-block;
  }

  .uno-booking-toolbar__toggle.is-open .uno-booking-toolbar__toggle-arrow {
    transform: rotate(180deg);
  }

  /* 가운데: 선택 요약 */
  .uno-booking-toolbar__summary {
    flex: 1;
    min-width: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 28px;
    overflow: hidden;
    min-height: 54px;
    border-radius: 14px;
    box-shadow:
      inset 5px 5px 12px rgba(21, 21, 21, 0.09),
      inset -5px -5px 12px rgba(255, 255, 255, 0.75);
  }

  .uno-booking-toolbar__summary-date {
    font-family: var(--font-en);
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.04em;
    color: #151515;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .uno-booking-toolbar__summary-dot {
    width: 3px;
    height: 3px;
    border-radius: 999px;
    background: rgba(21, 21, 21, 0.3);
    flex-shrink: 0;
  }

  .uno-booking-toolbar__summary-people {
    font-family: var(--font-ko);
    font-size: 12px;
    letter-spacing: -0.03em;
    color: rgba(21, 21, 21, 0.52);
    white-space: nowrap;
  }

  /* 오른쪽: 가격 + 버튼들 */
  .uno-booking-toolbar__right {
    display: flex;
    align-items: center;
    gap: 0;
    flex-shrink: 0;
  }

  .uno-booking-toolbar__price-block {
    text-align: right;
    min-width: 160px;
    margin-right: 22px;
  }

  .uno-booking-toolbar__price-label {
    font-family: var(--font-ko);
    font-size: 11px;
    letter-spacing: -0.03em;
    color: rgba(21, 21, 21, 0.46);
    line-height: 1;
  }

  .uno-booking-toolbar__price {
    font-family: var(--font-en);
    font-size: 22px;
    font-weight: 720;
    letter-spacing: -0.04em;
    color: #151515;
    line-height: 1.1;
  }

  .uno-booking-toolbar__btn {
    appearance: none;
    height: 48px;
    min-width: 126px;
    padding: 0 22px;
    border: 0;
    background: #f2efe7;
    color: #151515;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: space-between;
    font-family: var(--font-ko);
    font-size: 13px;
    font-weight: 800;
    letter-spacing: -0.035em;
    white-space: nowrap;
    text-decoration: none;
    box-sizing: border-box;
    border-radius: 0;
    margin-left: 8px;
    transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
    box-shadow:
      6px 6px 14px rgba(21, 21, 21, 0.11),
      -6px -6px 14px rgba(255, 255, 255, 0.82);
  }

  .uno-booking-toolbar__btn:hover {
    background: #151515;
    color: #ffffff;
  }

  .uno-booking-toolbar__btn:active {
    transform: translateY(1px);
    box-shadow:
      inset 4px 4px 10px rgba(21, 21, 21, 0.13),
      inset -4px -4px 10px rgba(255, 255, 255, 0.72);
  }

  .uno-booking-toolbar__dock-toggle {
    appearance: none;
    width: 56px;
    height: 56px;
    margin-left: 8px;
    border: 0;
    border-radius: 50%;
    background: #f2efe7;
    color: #151515;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-en);
    font-size: 24px;
    font-weight: 500;
    line-height: 1;
    box-shadow:
      7px 7px 16px rgba(21, 21, 21, 0.14),
      -7px -7px 16px rgba(255, 255, 255, 0.86);
    transition:
      transform 0.6s cubic-bezier(0.075, 0.82, 0.165, 1),
      box-shadow 0.25s ease;
  }

  .uno-booking-toolbar__dock-toggle.is-open {
    transform: rotate(45deg);
    box-shadow:
      inset 5px 5px 12px rgba(21, 21, 21, 0.13),
      inset -5px -5px 12px rgba(255, 255, 255, 0.78);
  }

  .uno-booking-toolbar__btn.is-cart.is-added {
    background: #fcc800;
    color: #151515;
  }

  .uno-booking-toolbar__btn.is-primary {
    background: #151515;
    color: #ffffff;
  }

  .uno-booking-toolbar__btn.is-primary:hover {
    background: #fcc800;
    border-color: #fcc800;
    color: #151515;
  }

  .uno-booking-toolbar__btn.is-kakao {
    background: #fee500;
    color: #151515;
  }

  .uno-booking-toolbar__btn.is-kakao:hover {
    background: #151515;
    border-color: #151515;
    color: #ffffff;
  }

  /* ─── Slide-up Panel ──────────────────────────────── */

  .uno-booking-backdrop {
    position: fixed;
    inset: 0;
    z-index: 888;
    border: 0;
    padding: 0;
    background: rgba(255, 255, 255, 0.42);
    backdrop-filter: blur(12px) saturate(0.82);
    -webkit-backdrop-filter: blur(12px) saturate(0.82);
    cursor: default;
  }

  .uno-booking-panel {
    position: fixed;
    bottom: 92px;
    left: 0;
    width: 100%;
    z-index: 889;
    background: #ffffff;
    border-top: 1px solid rgba(21, 21, 21, 0.12);
    box-shadow: 0 -16px 56px rgba(21, 21, 21, 0.10);
    transform: translateY(100%);
    opacity: 0;
    pointer-events: none;
    transition:
      transform 0.44s cubic-bezier(0.16, 1, 0.3, 1),
      opacity 0.28s ease;
    max-height: calc(100vh - 180px);
    overflow-y: auto;
    overscroll-behavior: contain;
    box-sizing: border-box;
  }

  .uno-booking-panel.is-open {
    transform: translateY(0);
    opacity: 1;
    pointer-events: auto;
  }

  .uno-booking-panel__inner {
    max-width: 760px;
    margin: 0 auto;
    padding: 48px 40px 36px;
    box-sizing: border-box;
  }

  .uno-booking-panel__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
  }

  .uno-booking-panel__title {
    font-family: var(--font-ko);
    font-size: 15px;
    font-weight: 700;
    letter-spacing: -0.04em;
    color: #151515;
  }

  .uno-booking-panel__close {
    appearance: none;
    width: 32px;
    height: 32px;
    border: 1px solid rgba(21, 21, 21, 0.14);
    border-radius: 999px;
    background: #ffffff;
    cursor: pointer;
    font-size: 0;
    line-height: 0;
    color: #151515;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: background 0.18s ease;
  }

  .uno-booking-panel__close::before,
  .uno-booking-panel__close::after {
    content: "";
    position: absolute;
    left: 50%;
    top: 50%;
    width: 11px;
    height: 1px;
    background: currentColor;
    transform-origin: center;
  }

  .uno-booking-panel__close::before {
    transform: translate(-50%, -50%) rotate(45deg);
  }

  .uno-booking-panel__close::after {
    transform: translate(-50%, -50%) rotate(-45deg);
  }

  .uno-booking-panel__close:hover {
    background: rgba(21, 21, 21, 0.06);
  }

  .uno-booking-panel__body {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 32px;
    align-items: start;
  }

  /* 단일 날짜 선택(세미패키지)일 때는 단일 컬럼 */
  .uno-booking-panel__body.is-single {
    grid-template-columns: 1fr;
  }

  .uno-booking-panel__section {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  /* 기존 booking-side 내부 스타일 재사용 (패널 내부용) */
  .uno-booking-side__label-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
  }

  .uno-booking-side__label {
    font-family: var(--font-ko);
    font-size: 13px;
    line-height: 1;
    letter-spacing: -0.035em;
    color: rgba(21, 21, 21, 0.68);
    font-weight: 700;
  }

  .uno-booking-side__hint {
    font-family: var(--font-ko);
    font-size: 11px;
    line-height: 1;
    letter-spacing: -0.025em;
    color: rgba(21, 21, 21, 0.42);
  }

  .uno-booking-side__select {
    width: 100%;
    height: 46px;
    border: 1px solid rgba(21, 21, 21, 0.16);
    border-radius: 0;
    background: #ffffff;
    padding: 0 14px;
    box-sizing: border-box;
    font-family: var(--font-ko);
    font-size: 13px;
    line-height: 1;
    letter-spacing: -0.035em;
    color: #151515;
    outline: none;
  }

  .uno-booking-side__calendar-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 12px;
  }

  .uno-booking-side__calendar-title {
    font-family: var(--font-ko);
    font-size: 13px;
    line-height: 1;
    letter-spacing: -0.035em;
    font-weight: 700;
    color: rgba(21, 21, 21, 0.72);
  }

  .uno-booking-side__calendar-month {
    font-family: var(--font-en);
    font-size: 11px;
    line-height: 1;
    letter-spacing: 0.14em;
    color: rgba(21, 21, 21, 0.42);
  }

  .uno-booking-side__calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 4px;
    padding: 10px;
    border: 1px solid rgba(21, 21, 21, 0.12);
    background: linear-gradient(180deg, rgba(255,255,255,1) 0%, rgba(248,248,245,0.72) 100%);
  }

  .uno-booking-side__weekday {
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-en);
    font-size: 10px;
    line-height: 1;
    letter-spacing: 0.04em;
    color: rgba(21, 21, 21, 0.38);
    text-transform: uppercase;
  }

  .uno-booking-side__day {
    position: relative;
    appearance: none;
    border: 1px solid transparent;
    border-radius: 999px;
    background: transparent;
    min-height: 38px;
    padding: 0;
    box-sizing: border-box;
    cursor: pointer;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #151515;
    transition: border-color 0.2s ease, background 0.2s ease, opacity 0.2s ease;
  }

  .uno-booking-side__day:hover:not(:disabled) {
    border-color: rgba(21, 21, 21, 0.22);
    background: rgba(255, 255, 255, 0.92);
  }

  .uno-booking-side__day.is-selected {
    border-color: #151515;
    background: #151515;
    color: #ffffff;
    box-shadow: 0 10px 24px rgba(21, 21, 21, 0.16);
  }

  .uno-booking-side__day.is-available:not(.is-selected)::after {
    content: "";
    position: absolute;
    bottom: 5px;
    width: 4px;
    height: 4px;
    border-radius: 999px;
    background: #fcc800;
  }

  .uno-booking-side__day:disabled {
    cursor: not-allowed;
    opacity: 0.24;
    background: transparent;
  }

  .uno-booking-side__day-num {
    font-family: var(--font-en);
    font-size: 12px;
    line-height: 1;
    font-weight: 650;
  }

  .uno-booking-side__day-status {
    display: none;
  }

  .uno-booking-side__selected-date {
    margin: 10px 0 0;
    padding: 11px 12px;
    border: 1px solid rgba(21, 21, 21, 0.1);
    background: rgba(255, 255, 255, 0.72);
    font-family: var(--font-ko);
    font-size: 12px;
    line-height: 1.45;
    letter-spacing: -0.03em;
    color: rgba(21, 21, 21, 0.62);
    word-break: keep-all;
  }

  .uno-booking-side__empty-day {
    min-height: 38px;
  }

  .uno-booking-side__people {
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 46px;
    border: 1px solid rgba(21, 21, 21, 0.14);
    padding: 0 8px 0 14px;
    box-sizing: border-box;
  }

  .uno-booking-side__people-label {
    font-family: var(--font-ko);
    font-size: 13px;
    letter-spacing: -0.035em;
    color: rgba(21, 21, 21, 0.72);
  }

  .uno-booking-side__counter {
    display: inline-flex;
    align-items: center;
    gap: 12px;
  }

  .uno-booking-side__counter-button {
    appearance: none;
    width: 30px;
    height: 30px;
    border: 1px solid rgba(21, 21, 21, 0.14);
    border-radius: 999px;
    background: #ffffff;
    cursor: pointer;
    font-family: var(--font-en);
    font-size: 15px;
    line-height: 1;
    color: #151515;
  }

  .uno-booking-side__counter-button:disabled {
    cursor: not-allowed;
    opacity: 0.35;
  }

  .uno-booking-side__count {
    min-width: 18px;
    text-align: center;
    font-family: var(--font-en);
    font-size: 15px;
    line-height: 1;
    font-weight: 700;
  }

  /* 패널 하단 가격 + 확인 */
  .uno-booking-panel__footer {
    margin-top: 28px;
    padding-top: 20px;
    border-top: 1px solid rgba(21, 21, 21, 0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
  }

  .uno-booking-panel__footer-price {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .uno-booking-panel__footer-price-label {
    font-family: var(--font-ko);
    font-size: 11px;
    letter-spacing: -0.03em;
    color: rgba(21, 21, 21, 0.46);
  }

  .uno-booking-panel__footer-price-value {
    font-family: var(--font-en);
    font-size: 26px;
    font-weight: 720;
    letter-spacing: -0.04em;
    color: #151515;
    line-height: 1;
  }

  .uno-booking-panel__footer-actions {
    display: flex;
    gap: 8px;
  }

  @media (max-width: 1280px) {
    .uno-booking-toolbar__inner {
      grid-template-columns: 180px minmax(130px, 1fr) auto 58px;
      padding: 0 24px;
    }

    .uno-booking-toolbar.is-compact .uno-booking-toolbar__inner {
      grid-template-columns: auto auto 58px;
      padding: 0 10px 0 20px;
    }

    .uno-booking-toolbar__summary {
      padding: 0 18px;
    }

    .uno-booking-toolbar__price-block {
      min-width: 130px;
      margin-right: 14px;
    }

    .uno-booking-toolbar__btn {
      min-width: 108px;
      padding: 0 14px;
    }
  }

  /*
    Final Direction — Premium Minimal Morphing Navigation
    -----------------------------------------------------
    One continuous navigation surface. No neumorphic tiles,
    decorative notch, or individual text containers.
  */
  .uno-booking-toolbar,
  .uno-booking-toolbar.is-compact {
    left: 50%;
    bottom: 24px;
    width: min(1180px, calc(100% - 48px));
    height: 84px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 999px;
    background: rgba(18, 18, 16, 0.94);
    box-shadow: 0 18px 54px rgba(0, 0, 0, 0.18);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    transform: translateX(-50%);
  }

  .uno-booking-toolbar.is-compact {
    width: min(378px, calc(100% - 32px));
    box-shadow: 0 14px 38px rgba(0, 0, 0, 0.2);
  }

  .uno-booking-toolbar.is-compact::before {
    content: none;
  }

  .uno-booking-toolbar__inner,
  .uno-booking-toolbar.is-compact .uno-booking-toolbar__inner {
    grid-template-columns: 184px minmax(150px, 1fr) auto 60px;
    width: 100%;
    min-height: 82px;
    padding: 0 12px 0 28px;
  }

  .uno-booking-toolbar.is-compact .uno-booking-toolbar__inner {
    display: grid;
    grid-template-columns: auto 60px;
    align-items: center;
    padding: 0 10px 0 18px;
  }

  .uno-booking-toolbar__toggle {
    height: 82px;
    padding: 0 18px 0 0;
    background: transparent;
    color: #ffffff;
    font-size: 12px;
    letter-spacing: -0.02em;
    opacity: 1;
    transform: scale(1);
    transition:
      opacity 0.24s ease,
      transform 0.5s cubic-bezier(0.075, 0.82, 0.165, 1);
  }

  .uno-booking-toolbar__toggle:hover,
  .uno-booking-toolbar__toggle.is-open {
    background: transparent;
    color: #ffffff;
  }

  .uno-booking-toolbar__summary {
    min-height: 0;
    padding: 0 20px;
    border-radius: 0;
    box-shadow: none;
    opacity: 1;
    transform: scale(1);
    transition:
      opacity 0.24s ease,
      transform 0.5s cubic-bezier(0.075, 0.82, 0.165, 1);
  }

  .uno-booking-toolbar__summary-date {
    color: #ffffff;
  }

  .uno-booking-toolbar__summary-people {
    color: rgba(255, 255, 255, 0.52);
  }

  .uno-booking-toolbar__summary-dot {
    background: rgba(255, 255, 255, 0.32);
  }

  .uno-booking-toolbar__price,
  .uno-booking-toolbar__price-label,
  .uno-booking-toolbar.is-compact .uno-booking-toolbar__price,
  .uno-booking-toolbar.is-compact .uno-booking-toolbar__price-label {
    color: #ffffff;
  }

  .uno-booking-toolbar__price-label {
    opacity: 0.46;
  }

  .uno-booking-toolbar__right {
    gap: 4px;
    opacity: 1;
    transform: scale(1);
    transition:
      opacity 0.24s ease,
      transform 0.5s cubic-bezier(0.075, 0.82, 0.165, 1);
  }

  .uno-booking-toolbar__price-block {
    min-width: 150px;
    margin-right: 14px;
  }

  .uno-booking-toolbar__btn {
    min-width: auto;
    height: 46px;
    margin-left: 0;
    padding: 0 17px;
    border-radius: 999px;
    background: transparent;
    color: rgba(255, 255, 255, 0.7);
    box-shadow: none;
    font-size: 12px;
    font-weight: 700;
  }

  .uno-booking-toolbar__btn:hover,
  .uno-booking-toolbar__btn:active {
    background: rgba(255, 255, 255, 0.09);
    color: #ffffff;
    box-shadow: none;
    transform: none;
  }

  .uno-booking-toolbar__btn.is-cart.is-added {
    background: transparent;
    color: #fcc800;
  }

  .uno-booking-toolbar__btn.is-kakao,
  .uno-booking-toolbar__btn.is-kakao:hover {
    background: transparent;
    color: #fee500;
  }

  .uno-booking-toolbar__btn.is-primary,
  .uno-booking-toolbar__btn.is-primary:hover,
  .uno-booking-toolbar.is-compact .uno-booking-toolbar__btn.is-primary {
    min-width: 126px;
    background: #ffffff;
    color: #151515;
  }

  .uno-booking-toolbar__btn:disabled,
  .uno-booking-toolbar__btn:disabled:hover,
  .uno-booking-toolbar__btn:disabled:active {
    cursor: not-allowed;
    opacity: 0.38;
    background: transparent;
    color: rgba(255, 255, 255, 0.62);
    box-shadow: none;
    transform: none;
  }

  .uno-booking-toolbar__dock-toggle,
  .uno-booking-toolbar.is-compact .uno-booking-toolbar__dock-toggle {
    width: 60px;
    height: 60px;
    margin: 0;
    flex: 0 0 60px;
    background: rgba(255, 255, 255, 0.06);
    color: #ffffff;
    box-shadow: none;
  }

  .uno-booking-toolbar__dock-toggle:hover {
    background: rgba(255, 255, 255, 0.12);
  }

  .uno-booking-toolbar__dock-toggle.is-open {
    box-shadow: none;
    transform: none;
  }

  .uno-booking-toolbar.is-compact .uno-booking-toolbar__toggle,
  .uno-booking-toolbar.is-compact .uno-booking-toolbar__summary {
    position: absolute;
    opacity: 0;
    transform: scale(0.55);
    pointer-events: none;
  }

  .uno-booking-toolbar.is-compact .uno-booking-toolbar__toggle {
    display: inline-flex;
  }

  .uno-booking-toolbar.is-compact .uno-booking-toolbar__summary {
    display: flex;
  }

  .uno-booking-toolbar.is-compact .uno-booking-toolbar__right {
    display: flex;
    grid-column: 1;
    justify-content: flex-end;
    opacity: 1;
    transform: none;
    pointer-events: auto;
  }

  .uno-booking-toolbar.is-compact .uno-booking-toolbar__price-block {
    min-width: 118px;
    margin-right: 8px;
  }

  .uno-booking-panel {
    bottom: 124px;
  }

  .pd-reservation-login-backdrop {
    position: fixed;
    inset: 0;
    z-index: 1200;
    display: grid;
    place-items: center;
    padding: 24px;
    background: rgba(21, 21, 21, 0.22);
    backdrop-filter: blur(14px);
  }

  .pd-reservation-login-modal {
    width: min(420px, 100%);
    border: 1px solid rgba(21, 21, 21, 0.16);
    background: #ffffff;
    padding: 28px;
    box-sizing: border-box;
    color: #151515;
  }

  .pd-reservation-login-modal span {
    display: block;
    font-family: var(--font-en);
    font-size: 11px;
    line-height: 1;
    letter-spacing: 0.18em;
    font-weight: 900;
    color: rgba(21, 21, 21, 0.46);
  }

  .pd-reservation-login-modal h2 {
    margin: 18px 0 12px;
    font-family: var(--font-ko);
    font-size: 30px;
    line-height: 1.08;
    letter-spacing: -0.06em;
    font-weight: 860;
  }

  .pd-reservation-login-modal p {
    margin: 0;
    font-family: var(--font-ko);
    font-size: 15px;
    line-height: 1.65;
    letter-spacing: -0.04em;
    color: rgba(21, 21, 21, 0.62);
    word-break: keep-all;
  }

  .pd-reservation-login-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 24px;
  }

  .pd-reservation-login-actions button {
    appearance: none;
    min-height: 48px;
    border: 1px solid rgba(21, 21, 21, 0.18);
    background: #ffffff;
    color: #151515;
    cursor: pointer;
    font-family: var(--font-ko);
    font-size: 14px;
    letter-spacing: -0.035em;
    font-weight: 760;
  }

  .pd-reservation-login-actions button.is-primary {
    border-color: #151515;
    background: #151515;
    color: #ffffff;
  }
`;
}

function BookingSideStyle() {
  return <style id={STYLE_ID}>{getBookingSideStyle()}</style>;
}

function DockDotGrid({ isOpen }: { isOpen: boolean }) {
  const [isHovered, setIsHovered] = useState(false);
  const hiddenOnHover = new Set([1, 3, 7]);

  return (
    <span
      aria-hidden="true"
      onMouseEnter={() => setIsHovered(true)}
      onMouseLeave={() => setIsHovered(false)}
      style={{
        display: "grid",
        gridTemplateColumns: "repeat(3, 4px)",
        gap: 4,
        width: 22,
        height: 22,
        alignContent: "center",
        justifyContent: "center",
        transform: isHovered || isOpen ? "scale(1.08)" : "scale(1)",
        transition: "transform 0.25s ease",
      }}
    >
      {Array.from({ length: 9 }).map((_, index) => {
        const openVisible =
          index === 0 ||
          index === 2 ||
          index === 4 ||
          index === 6 ||
          index === 8;

        return (
          <span
            key={index}
            style={{
              width: 4,
              height: 4,
              borderRadius: "50%",
              background: "currentColor",
              opacity: isOpen
                ? openVisible
                  ? 1
                  : 0
                : isHovered && hiddenOnHover.has(index)
                  ? 0
                  : 1,
              transform: isOpen
                ? index === 0 || index === 8
                  ? "scaleX(2.15) rotate(45deg)"
                  : index === 2 || index === 6
                    ? "scaleX(2.15) rotate(-45deg)"
                    : "scale(0.88)"
                : isHovered && hiddenOnHover.has(index)
                  ? "scale(0.4)"
                  : "scale(1)",
              transition: "opacity 0.24s ease, transform 0.24s ease",
            }}
          />
        );
      })}
    </span>
  );
}

export default function BookingSide({
  product,
  availableDates,
  kakaoChannelUrl = DEFAULT_KAKAO_CHANNEL_URL,
  cartHref = DEFAULT_MY_CART_PAGE_URL,
  reservationHref = DEFAULT_RESERVATION_PAGE_URL,
}: BookingSideProps) {
  const panelRef = useRef<HTMLDivElement>(null);
  const toggleRef = useRef<HTMLButtonElement>(null);

  const selectableDates = useMemo(
    () => availableDates.filter((date) => !isDateSoldOut(date)),
    [availableDates],
  );

  const [selectedDateId, setSelectedDateId] = useState(
    selectableDates[0]?.id ?? availableDates[0]?.id ?? "",
  );
  const [peopleCount, setPeopleCount] = useState(1);
  const [isCartAdded, setIsCartAdded] = useState(false);
  const [isPanelOpen, setIsPanelOpen] = useState(false);
  const [isScrolled, setIsScrolled] = useState(false);
  const [isDockOpen, setIsDockOpen] = useState(false);
  const [isLoginModalOpen, setIsLoginModalOpen] = useState(false);
  const [portalTarget, setPortalTarget] = useState<HTMLElement | null>(null);

  useEffect(() => {
    setPortalTarget(document.body);
  }, []);

  useEffect(() => {
    const handleScroll = () => {
      const nextScrolled = window.scrollY >= 180;
      setIsScrolled(nextScrolled);

      if (nextScrolled) {
        setIsDockOpen(false);
        setIsPanelOpen(false);
      }
    };

    handleScroll();
    window.addEventListener("scroll", handleScroll, { passive: true });

    return () => {
      window.removeEventListener("scroll", handleScroll);
    };
  }, []);

  const isDailyTour = product.productType === "daily";
  const selectedDate =
    availableDates.find((date) => date.id === selectedDateId) ??
    selectableDates[0] ??
    availableDates[0];
  const selectedPrice = selectedDate?.price ?? product.basePrice ?? 0;
  const totalPrice = selectedPrice * peopleCount;
  const maxPeople = Math.max(1, selectedDate?.seats ?? 99);
  const isSelectedSoldOut = !selectedDate || isDateSoldOut(selectedDate);

  const calendarMeta = useMemo(() => {
    const firstDate = availableDates[0];
    const firstKey = firstDate ? normalizeDateKey(firstDate.id) : "";
    const first = firstKey ? new Date(`${firstKey}T00:00:00`) : new Date();
    const year = first.getFullYear();
    const month = first.getMonth();
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const monthLabel = `${year}.${String(month + 1).padStart(2, "0")}`;
    return { year, month, firstDay, daysInMonth, monthLabel };
  }, [availableDates]);

  const datesByDay = useMemo(() => {
    const map = new Map<number, AvailableDate>();
    availableDates.forEach((date) => {
      const key = normalizeDateKey(date.id);
      const parsed = new Date(`${key}T00:00:00`);
      if (!Number.isNaN(parsed.getTime())) map.set(parsed.getDate(), date);
    });
    return map;
  }, [availableDates]);

  /* 패널 외부 클릭·ESC 닫힘 */
  useEffect(() => {
    if (!isPanelOpen) return;

    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";

    const handlePointerDown = (event: MouseEvent) => {
      const target = event.target as Node;
      if (
        panelRef.current?.contains(target) ||
        toggleRef.current?.contains(target)
      ) return;
      setIsPanelOpen(false);
    };

    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape") setIsPanelOpen(false);
    };

    document.addEventListener("mousedown", handlePointerDown);
    window.addEventListener("keydown", handleKeyDown);
    return () => {
      document.body.style.overflow = previousOverflow;
      document.removeEventListener("mousedown", handlePointerDown);
      window.removeEventListener("keydown", handleKeyDown);
    };
  }, [isPanelOpen]);

  const getReservationPayload = () =>
    createReservationPayload({
      product: {
        id: product.id,
        legacyProductId: product.legacyProductId,
        productType: product.productType,
        title: product.title,
        href:
          typeof window === "undefined"
            ? ""
            : `${window.location.pathname}${window.location.search}`,
        currency: product.currency,
        basePrice: product.basePrice,
      },
      selectedDate,
      personCount: peopleCount,
      unitPrice: selectedPrice,
      totalPrice,
    });

  const handleCart = () => {
    if (isSelectedSoldOut) return;

    if (isCartAdded) {
      navigateInternal(cartHref);
      return;
    }

    saveCartReservation(getReservationPayload());
    setIsCartAdded(true);
  };

  const handleReservation = () => {
    if (isSelectedSoldOut) return;

    if (!isReservationUserLoggedIn()) {
      setIsLoginModalOpen(true);
      return;
    }

    savePendingReservation(getReservationPayload());
    navigateInternal(reservationHref);
  };

  const handleLoginMove = () => {
    savePendingReservation(getReservationPayload());
    navigateToLoginForReservation(reservationHref);
  };

  /* 툴바 요약 표시용 날짜 레이블 */
  const summaryDateLabel = selectedDate
    ? formatCompactDateLabel(selectedDate.label)
    : "날짜 미선택";
  const isCompact = isScrolled && !isDockOpen && !isPanelOpen;

  return (
    <>
      <BookingSideStyle />

      {portalTarget
        ? createPortal(
            <>
      {isLoginModalOpen ? (
        <div
          className="pd-reservation-login-backdrop"
          role="dialog"
          aria-modal="true"
          aria-label="로그인 안내"
        >
          <div className="pd-reservation-login-modal">
            <span>LOGIN REQUIRED</span>
            <h2>예약을 위해 로그인이 필요합니다</h2>
            <p>
              예약 정보 저장과 마이페이지 확인을 위해 로그인 후 예약을 진행해
              주세요.
            </p>
            <div className="pd-reservation-login-actions">
              <button type="button" onClick={() => setIsLoginModalOpen(false)}>
                계속 보기
              </button>
              <button type="button" className="is-primary" onClick={handleLoginMove}>
                로그인 화면으로 이동
              </button>
            </div>
          </div>
        </div>
      ) : null}
      {isPanelOpen ? (
        <button
          type="button"
          className="uno-booking-backdrop"
          aria-label="예약 옵션 닫기"
          onClick={() => setIsPanelOpen(false)}
        />
      ) : null}

      {/* ── Slide-up Panel ───────────────────────────── */}
      <div
        ref={panelRef}
        className={`uno-booking-panel${isPanelOpen ? " is-open" : ""}`}
        aria-hidden={!isPanelOpen}
        aria-label="예약 옵션 패널"
      >
        <div className="uno-booking-panel__inner">
          <div className="uno-booking-panel__head">
            <span className="uno-booking-panel__title">날짜 · 인원 선택</span>
            <button
              type="button"
              className="uno-booking-panel__close"
              onClick={() => setIsPanelOpen(false)}
              aria-label="패널 닫기"
            />
          </div>

          <div className={`uno-booking-panel__body${isDailyTour ? "" : " is-single"}`}>
            {/* 날짜 */}
            <div className="uno-booking-panel__section">
              {isDailyTour ? (
                <>
                  <div className="uno-booking-side__calendar-head">
                    <strong className="uno-booking-side__calendar-title">예약 날짜</strong>
                    <span className="uno-booking-side__calendar-month">{calendarMeta.monthLabel}</span>
                  </div>
                  <div className="uno-booking-side__calendar-grid">
                    {["일", "월", "화", "수", "목", "금", "토"].map((day) => (
                      <div key={day} className="uno-booking-side__weekday">{day}</div>
                    ))}
                    {Array.from({ length: calendarMeta.firstDay }).map((_, i) => (
                      <div key={`e-${i}`} className="uno-booking-side__empty-day" />
                    ))}
                    {Array.from({ length: calendarMeta.daysInMonth }).map((_, i) => {
                      const dayNumber = i + 1;
                      const date = datesByDay.get(dayNumber);
                      const disabled = !date || isDateSoldOut(date);
                      const isSelected = !!date && date.id === selectedDate?.id;
                      const dateStatusLabel = date
                        ? getAvailabilityDisplayLabel(getAvailabilityStatus(date))
                        : "";
                      return (
                        <button
                          key={dayNumber}
                          type="button"
                          className={`uno-booking-side__day${date && !disabled ? " is-available" : ""}${isSelected ? " is-selected" : ""}`}
                          disabled={disabled}
                          onClick={() => {
                            if (date) { setSelectedDateId(date.id); setPeopleCount(1); setIsCartAdded(false); }
                          }}
                          aria-label={date ? `${date.label} ${dateStatusLabel}` : `${dayNumber}일 예약 불가`}
                        >
                          <span className="uno-booking-side__day-num">{dayNumber}</span>
                          <span className="uno-booking-side__day-status">{dateStatusLabel}</span>
                        </button>
                      );
                    })}
                  </div>
                  {selectedDate && (
                    <p className="uno-booking-side__selected-date">
                      선택: {selectedDate.label} ({selectedDate.day}) · {getAvailabilityDisplayLabel(getAvailabilityStatus(selectedDate))} · 잔여 {selectedDate.seats}석
                    </p>
                  )}
                </>
              ) : (
                <>
                  <div className="uno-booking-side__label-row">
                    <strong className="uno-booking-side__label">예약 날짜</strong>
                    <span className="uno-booking-side__hint">출발일 기준</span>
                  </div>
                  <select
                    className="uno-booking-side__select"
                    value={selectedDateId}
                    onChange={(e) => { setSelectedDateId(e.target.value); setPeopleCount(1); setIsCartAdded(false); }}
                  >
                    {availableDates.map((date) => (
                      <option key={date.id} value={date.id} disabled={isDateSoldOut(date)}>
                        {formatCompactDateLabel(date.label)} ({date.day}) · {getAvailabilityDisplayLabel(getAvailabilityStatus(date))} · 잔여 {date.seats}석
                      </option>
                    ))}
                  </select>
                </>
              )}
            </div>

            {/* 인원 */}
            <div className="uno-booking-panel__section">
              <div className="uno-booking-side__label-row">
                <strong className="uno-booking-side__label">인원</strong>
                <span className="uno-booking-side__hint">최대 {maxPeople}명</span>
              </div>
              <div className="uno-booking-side__people">
                <span className="uno-booking-side__people-label">예약 인원</span>
                <span className="uno-booking-side__counter">
                  <button
                    type="button"
                    className="uno-booking-side__counter-button"
                    disabled={peopleCount <= 1}
                    onClick={() => { setPeopleCount((c) => Math.max(1, c - 1)); setIsCartAdded(false); }}
                    aria-label="인원 줄이기"
                  >−</button>
                  <strong className="uno-booking-side__count">{peopleCount}</strong>
                  <button
                    type="button"
                    className="uno-booking-side__counter-button"
                    disabled={peopleCount >= maxPeople}
                    onClick={() => { setPeopleCount((c) => Math.min(maxPeople, c + 1)); setIsCartAdded(false); }}
                    aria-label="인원 늘리기"
                  >+</button>
                </span>
              </div>
            </div>
          </div>

          {/* 패널 내 가격 + 확인 */}
          <div className="uno-booking-panel__footer">
            <div className="uno-booking-panel__footer-price">
              <span className="uno-booking-panel__footer-price-label">{peopleCount}명 합계</span>
              <strong className="uno-booking-panel__footer-price-value">
                <PriceText price={totalPrice} currency={product.currency ?? "KRW"} />
              </strong>
            </div>
            <div className="uno-booking-panel__footer-actions">
              <button
                type="button"
                className={`uno-booking-toolbar__btn is-cart${isCartAdded ? " is-added" : ""}`}
                disabled={isSelectedSoldOut}
                onClick={handleCart}
              >
                {isCartAdded ? "장바구니 보기" : "장바구니 담기"}
              </button>
              <button
                type="button"
                className="uno-booking-toolbar__btn is-primary"
                disabled={isSelectedSoldOut}
                onClick={() => { setIsPanelOpen(false); handleReservation(); }}
              >
                예약 진행하기
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* ── Floating Toolbar ─────────────────────────── */}
      <div
        className={`uno-booking-toolbar${isCompact ? " is-compact" : ""}`}
        role="region"
        aria-label="예약 바"
      >
        <div className="uno-booking-toolbar__inner">
          {/* 왼쪽: 날짜·인원 토글 */}
          <button
            ref={toggleRef}
            type="button"
            className={`uno-booking-toolbar__toggle${isPanelOpen ? " is-open" : ""}`}
            onClick={() => {
              setIsDockOpen(true);
              setIsPanelOpen((prev) => !prev);
            }}
            aria-expanded={isPanelOpen}
          >
            날짜 · 인원 선택
            <span className="uno-booking-toolbar__toggle-arrow">▲</span>
          </button>

          {/* 가운데: 선택 요약 */}
          <div className="uno-booking-toolbar__summary">
            <span className="uno-booking-toolbar__summary-date">{summaryDateLabel}</span>
            <span className="uno-booking-toolbar__summary-dot" aria-hidden="true" />
            <span className="uno-booking-toolbar__summary-people">{peopleCount}명</span>
          </div>

          {/* 오른쪽: 가격 + 버튼 */}
          <div className="uno-booking-toolbar__right">
            <div className="uno-booking-toolbar__price-block">
              <div className="uno-booking-toolbar__price-label">합계</div>
              <div className="uno-booking-toolbar__price">
                <PriceText price={totalPrice} currency={product.currency ?? "KRW"} />
              </div>
            </div>
            <button
              type="button"
              className={`uno-booking-toolbar__btn is-cart${isCartAdded ? " is-added" : ""}`}
              disabled={isSelectedSoldOut}
              onClick={handleCart}
            >
              {isCartAdded ? "장바구니 보기" : "장바구니 담기"}
            </button>
            <a
              className="uno-booking-toolbar__btn is-kakao"
              href={kakaoChannelUrl}
              target="_blank"
              rel="noreferrer"
            >
              카카오 문의
            </a>
            <button
              type="button"
              className="uno-booking-toolbar__btn is-primary"
              disabled={isSelectedSoldOut}
              onClick={() => { setIsPanelOpen(false); handleReservation(); }}
            >
              예약 진행하기
            </button>
          </div>
          <button
            type="button"
            className={`uno-booking-toolbar__dock-toggle${isCompact ? "" : " is-open"}`}
            onClick={() => {
              setIsPanelOpen(false);
              setIsDockOpen((prev) => !prev);
            }}
            aria-label={isCompact ? "예약 메뉴 펼치기" : "예약 메뉴 접기"}
            aria-expanded={!isCompact}
          >
            <DockDotGrid isOpen={!isCompact} />
          </button>
        </div>
      </div>
            </>,
            portalTarget,
          )
        : null}
    </>
  );
}
