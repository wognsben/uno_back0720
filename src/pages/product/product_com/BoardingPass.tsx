// BoardingPass.tsx
// 세미패키지 예약 UI 내부의 데코용 일정표 컴포넌트
// 절대 항공권/발권/좌석 예약 기능이 아니다. 프리미엄한 보딩패스 형태로
// 한국 -> 목적지, 목적지 -> 한국 이동 일정을 보여주는 시각 장식이다.
// 일정 원본은 ProductDetail.ticket이며, 백엔드 연동 전에는 fallback 일정으로 화면 깨짐을 방지한다.

import imgKoreanAir from "./calendar_src/KoreanAir.png";

const BOARDING_PASS_STYLE = `
.uno-boarding-pass {
  display: grid;
  grid-template-columns: 92px minmax(0, 1fr);
  width: 100%;
  min-height: 430px;
  border: 1px solid rgba(21, 21, 21, 0.16);
  border-radius: 0;
  background: #ffffff;
  color: #151515;
  overflow: hidden;
  box-sizing: border-box;
}

.uno-boarding-barcode {
  position: relative;
  min-width: 0;
  margin: 34px 0;
  border-right: 1px dashed rgba(21, 21, 21, 0.18);
  background: repeating-linear-gradient(
    90deg,
    #151515 0,
    #151515 1.6px,
    transparent 1.6px,
    transparent 4px
  );
  background-size: 56px 100%;
  background-repeat: no-repeat;
  background-position: center;
}

.uno-boarding-main {
  min-width: 0;
  display: grid;
  grid-template-rows: 36px minmax(0, 1fr) minmax(0, 1fr);
  row-gap: 22px;
  padding: 30px 42px 34px;
  box-sizing: border-box;
}

.uno-boarding-header {
  display: flex;
  align-items: flex-start;
  justify-content: flex-end;
  min-width: 0;
}

.uno-boarding-header img {
  display: block;
  width: 190px;
  max-width: 240px;
  height: auto;
  object-fit: contain;
  transform: translateY(-2px);
}

.uno-boarding-header span {
  display: none;
}

.uno-boarding-segment {
  display: grid;
  grid-template-rows: auto minmax(64px, auto) auto;
  row-gap: 10px;
  min-width: 0;
}

.uno-boarding-segment + .uno-boarding-segment {
  padding-top: 18px;
  border-top: 1px dashed rgba(21, 21, 21, 0.22);
}

.uno-boarding-segment-label {
  font-family: var(--font-en), var(--font-ko);
  font-size: 12px;
  line-height: 1;
  letter-spacing: 0.14em;
  font-weight: 760;
  color: #003b7a;
  text-transform: uppercase;
  white-space: nowrap;
}

.uno-boarding-route {
  display: grid;
  grid-template-columns: minmax(170px, 1fr) minmax(220px, 0.9fr) minmax(170px, 1fr);
  align-items: start;
  gap: 40px;
  min-width: 0;
}

.uno-boarding-airport {
  min-width: 0;
  padding-inline: 22px;
  padding-top: 0;
}

.uno-boarding-airport.is-arrival {
  text-align: right;
  padding-top: 0;
}

.uno-boarding-airport strong {
  display: block;
  font-family: var(--font-en);
  font-size: clamp(32px, 5.2vw, 42px);
  line-height: 0.86;
  letter-spacing: -0.065em;
  font-weight: 820;
  color: #151515;
  white-space: nowrap;
}

.uno-boarding-airport span {
  display: block;
  margin-top: 10px;
  font-family: var(--font-en);
  font-size: 12px;
  line-height: 1;
  letter-spacing: 0.08em;
  font-weight: 620;
  color: #151515;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.uno-boarding-route i {
  position: relative;
  display: block;
  width: 100%;
  max-width: 180px;
  justify-self: center;
  align-self: center;
  height: 1px;
  background: rgba(21, 21, 21, 0.34);
}

.uno-boarding-route i::before {
  content: "✈";
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -52%);
  display: flex;
  align-items: center;
  justify-content: center;
  width: 30px;
  height: 22px;
  background: #ffffff;
  font-family: var(--font-en);
  font-size: 20px;
  line-height: 1;
  color: #151515;
}

.uno-boarding-time-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  column-gap: 34px;
  min-width: 0;
}

.uno-boarding-time-grid > div:nth-child(2) {
  border-left: 1px solid rgba(21, 21, 21, 0.14);
  padding-left: 34px;
}

.uno-boarding-time-grid span {
  display: block;
  margin-bottom: 8px;
  font-family: var(--font-en);
  font-size: 10px;
  line-height: 1;
  letter-spacing: 0.18em;
  font-weight: 760;
  color: #003b7a;
  text-transform: uppercase;
}

.uno-boarding-time-grid strong {
  display: block;
  font-family: var(--font-en);
  font-size: 30px;
  line-height: 0.95;
  letter-spacing: -0.045em;
  font-weight: 760;
  color: #151515;
}

.uno-boarding-time-grid em {
  display: block;
  margin-top: 7px;
  font-family: var(--font-ko);
  font-size: 14px;
  line-height: 1.15;
  letter-spacing: -0.035em;
  font-style: normal;
  font-weight: 520;
  color: rgba(21, 21, 21, 0.76);
}

@media (max-width: 1280px) {
  .uno-boarding-pass {
    grid-template-columns: 68px minmax(0, 1fr);
    min-height: 398px;
  }

  .uno-boarding-main {
    row-gap: 15px;
    padding: 26px 26px 28px;
  }

  .uno-boarding-header img {
    width: 238px;
  }

  .uno-boarding-header span {
    font-size: 14px;
    letter-spacing: 0.16em;
  }

  .uno-boarding-airport strong {
    font-size: 44px;
  }

  .uno-boarding-route {
    display: grid;
    grid-template-columns: minmax(132px, 0.86fr) minmax(120px, 0.5fr) minmax(132px, 0.86fr);
    align-items: start;
    gap: 20px;
    min-width: 0;
  }

  .uno-boarding-route i {
    align-self: center;
  }

  .uno-boarding-time-grid {
    column-gap: 60px;
  }

  .uno-boarding-time-grid > div:nth-child(2) {
    padding-left: 60px;
  }

  .uno-boarding-time-grid strong {
    font-size: 28px;
  }
}
`;

export type BoardingPassFlightSegment = {
  label: string;
  fromCode: string;
  fromCity: string;
  toCode: string;
  toCity: string;
  departTime: string;
  departDate: string;
  arriveTime: string;
  arriveDate: string;
};

export type BookingBoardingPassProps = {
  airlineLogoSrc?: string;
  airlineLogoAlt?: string;
  title?: string;
  outbound?: BoardingPassFlightSegment;
  inbound?: BoardingPassFlightSegment;
};

/*
  Boarding Pass Fallback Data
  ------------------------------------------
  백엔드 항공 일정 연동 전 화면이 비어 보이지 않도록 사용하는 임시 기본값이다.
  실제 연동 후에는 ProductDetail.ticket.outbound / inbound 값이 우선 적용된다.
*/
const FALLBACK_OUTBOUND: BoardingPassFlightSegment = {
  label: "OUTBOUND · 가는 편",
  fromCode: "ICN",
  fromCity: "서울/인천",
  toCode: "FCO",
  toCity: "로마/피우미치노 레오나르도 다 빈치",
  departTime: "23:20",
  departDate: "10월 17일 (토)",
  arriveTime: "09:50",
  arriveDate: "10월 18일 (일)",
};

const FALLBACK_INBOUND: BoardingPassFlightSegment = {
  label: "INBOUND · 오는 편",
  fromCode: "FCO",
  fromCity: "로마/피우미치노 레오나르도 다 빈치",
  toCode: "ICN",
  toCity: "서울/인천",
  departTime: "19:25",
  departDate: "10월 25일 (일)",
  arriveTime: "18:20",
  arriveDate: "10월 26일 (월)",
};

function FlightSegment({
  label,
  fromCode,
  fromCity,
  toCode,
  toCity,
  departTime,
  departDate,
  arriveTime,
  arriveDate,
}: BoardingPassFlightSegment) {
  return (
    <section className="uno-boarding-segment" aria-label={label}>
      <div className="uno-boarding-segment-label">{label}</div>

      <div className="uno-boarding-route">
        <div className="uno-boarding-airport">
          <strong>{fromCode}</strong>
          <span>{fromCity}</span>
        </div>

        <i aria-hidden="true" />

        <div className="uno-boarding-airport is-arrival">
          <strong>{toCode}</strong>
          <span>{toCity}</span>
        </div>
      </div>

      <div className="uno-boarding-time-grid">
        <div>
          <span>DEPART</span>
          <strong>{departTime}</strong>
          <em>{departDate}</em>
        </div>

        <div>
          <span>ARRIVE</span>
          <strong>{arriveTime}</strong>
          <em>{arriveDate}</em>
        </div>
      </div>
    </section>
  );
}

export default function BookingBoardingPass({
  airlineLogoSrc = imgKoreanAir,
  airlineLogoAlt = "Korean Air",
  title = "BOARDING PASS",
  outbound,
  inbound,
}: BookingBoardingPassProps) {
  const safeOutbound = outbound ?? FALLBACK_OUTBOUND;
  const safeInbound = inbound ?? FALLBACK_INBOUND;

  return (
    <>
      <style>{BOARDING_PASS_STYLE}</style>
      <article className="uno-boarding-pass" aria-label="왕복 항공 보딩패스">
        <aside className="uno-boarding-barcode" aria-hidden="true" />

        <main className="uno-boarding-main">
          <header className="uno-boarding-header">
            <img src={airlineLogoSrc} alt={airlineLogoAlt} />
            <span>{title}</span>
          </header>

          <FlightSegment {...safeOutbound} />
          <FlightSegment {...safeInbound} />
        </main>
      </article>
    </>
  );
}
