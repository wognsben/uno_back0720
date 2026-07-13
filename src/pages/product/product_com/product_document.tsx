import { useMemo, useState } from "react";

export type ProductDetailTab =
  | "review"
  | "course"
  | "guide"
  | "included"
  | "excluded"
  | "notice"
  | "meeting";

export type DetailScheduleDay = {
  day: string;
  city: string;
  time: string;
  title: string;
  body: string;
};

export type DetailNotice = {
  title: string;
  body: string;
};

export type ReviewItem = {
  id: string;
  nickname: string;
  writtenAt: string;
  productTitle: string;
  rating: number;
  title: string;
  body: string;
};

export type MeetingPoint = {
  name: string;
  address: string;
  time: string;
  lat: number;
  lng: number;
  mapUrl: string;
  directionUrl: string;
};

export type ProductDocumentImage = {
  src: string;
  title?: string;
  source?: string;
};

export type ProductDocumentData = {
  productType: "semi" | "daily";
  guide: string;
  included: string;
  excluded: string;
  review: string;
  reviews: ReviewItem[];
  reservationNotice: string;
  scheduleIntro: string;
  scheduleDays: DetailScheduleDay[];
  courseImages?: ProductDocumentImage[];
  featureImages?: ProductDocumentImage[];
  notices: DetailNotice[];
  meetingPoint: MeetingPoint;
};

type ProductDocumentProps = {
  detailData: ProductDocumentData;
  selectedGuide: string;
  isDailyTour: boolean;
  onOpenReview: () => void;
  onOpenNotice: (notice: DetailNotice) => void;
};

const PRODUCT_DOCUMENT_TABS: Array<{
  key: ProductDetailTab;
  label: string;
  index: string;
}> = [
  { key: "guide", label: "가이드 정보", index: "01" },
  { key: "review", label: "리뷰", index: "02" },
  { key: "meeting", label: "미팅 장소", index: "03" },
  { key: "notice", label: "예약 안내", index: "04" },
  { key: "included", label: "포함/불포함", index: "05" },
];

const INCLUDED_ITEMS = [
  {
    title: "전문 가이드 해설",
    desc: "상품별 일정과 현장 동선에 맞춰 주요 포인트를 안내합니다.",
  },
  {
    title: "현장 일정 관리",
    desc: "출발 시간, 이동 순서, 현장 상황을 기준으로 일정을 관리합니다.",
  },
  {
    title: "주요 구간 동선 안내",
    desc: "복잡한 이동 구간은 사전 안내와 현장 브리핑을 제공합니다.",
  },
  {
    title: "예약 조건 확인",
    desc: "출발일, 잔여석, 포함 조건, 최종 금액은 예약 확정 시 다시 확인합니다.",
  },
];

const EXCLUDED_ITEMS = [
  {
    title: "항공권",
    desc: "상품 조건에 따라 별도 구매 또는 별도 안내될 수 있습니다.",
  },
  {
    title: "개인 경비",
    desc: "자유 일정과 개인 취향에 따라 발생하는 비용은 포함되지 않습니다.",
  },
  {
    title: "여행자 보험",
    desc: "개별 가입을 권장하며, 예약 시 필요하면 별도 안내합니다.",
  },
  {
    title: "자유 일정 비용",
    desc: "개인 입장료, 교통비, 선택 체험 비용은 현장 상황에 따라 별도입니다.",
  },
];

const STYLE = `
  .pd-product-document-strip {
    width: 1700px;
    max-width: 100%;
    margin: 0 auto;
    padding: 0 50px 36px;
    box-sizing: border-box;
    border-top: 0;
  }

  .pd-product-document-label {
    width: 100%;
    min-height: 72px;
    margin: 0;
    border-top: 1px solid rgba(21, 21, 21, 0.18);
    border-bottom: 1px solid rgba(21, 21, 21, 0.18);
    background: #ffffff;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-family: var(--font-en);
    font-size: 18px;
    line-height: 1;
    letter-spacing: 0.16em;
    color: #151515;
    text-align: left;
  }

  .pd-product-document-label strong {
    font: inherit;
    font-weight: 700;
    color: #151515;
  }

  .pd-product-document-label span {
    font-family: var(--font-ko);
    font-size: 13px;
    letter-spacing: -0.02em;
    color: rgba(21, 21, 21, 0.5);
  }

  .pd-product-document-row {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    border-top: 1px solid rgba(21, 21, 21, 0.14);
    border-bottom: 1px solid rgba(21, 21, 21, 0.14);
  }

  .pd-product-document-item {
    appearance: none;
    min-height: 92px;
    border: 0;
    border-right: 1px solid rgba(21, 21, 21, 0.1);
    background: #ffffff;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: space-between;
    padding: 20px 22px;
    color: rgba(21, 21, 21, 0.44);
    text-align: left;
  }

  .pd-product-document-item:last-child {
    border-right: 0;
  }

  .pd-product-document-item span {
    font-family: var(--font-ko);
    font-size: 16px;
    line-height: 1;
    letter-spacing: -0.04em;
  }

  .pd-product-document-item strong {
    font-family: var(--font-en);
    font-size: 13px;
    line-height: 1;
    letter-spacing: 0.16em;
    font-weight: 500;
  }

  .pd-product-document-item.is-active,
  .pd-product-document-item:hover {
    color: #151515;
  }

  .pd-product-document-item.is-active {
    box-shadow: inset 0 -2px 0 #151515;
  }

  .pd-product-document-content {
    width: 1600px;
    margin: 0 50px 84px;
    padding: 38px 0 54px;
    border-top: 1px solid rgba(21, 21, 21, 0.14);
    border-bottom: 1px solid rgba(21, 21, 21, 0.14);
    display: grid;
    grid-template-columns: 260px minmax(0, 1fr);
    gap: 42px;
    box-sizing: border-box;
  }

  .pd-product-document-content-left {
    border-right: 1px solid rgba(21, 21, 21, 0.1);
    padding-right: 28px;
  }

  .pd-document-content-index {
    font-family: var(--font-en);
    font-size: 68px;
    line-height: 0.86;
    letter-spacing: -0.065em;
    color: #151515;
  }

  .pd-document-content-kicker {
    margin-top: 18px;
    font-family: var(--font-ko);
    font-size: 15px;
    line-height: 1.5;
    letter-spacing: -0.04em;
    color: rgba(21, 21, 21, 0.56);
    word-break: keep-all;
  }

  .pd-product-document-content-main {
    min-width: 0;
  }

  .pd-document-image-groups {
    display: none;
    width: 1600px;
    margin: 0 50px 84px;
    display: grid;
    gap: 58px;
  }

  .pd-document-image-group {
    display: grid;
    gap: 14px;
  }

  .pd-document-image-heading {
    margin: 0;
    font-family: var(--font-en);
    font-size: 13px;
    line-height: 1;
    letter-spacing: 0.18em;
    color: rgba(21, 21, 21, 0.52);
    text-transform: uppercase;
  }

  .pd-document-image-list {
    display: grid;
    gap: 0;
    justify-items: center;
  }

  .pd-document-image-frame {
    width: 100%;
    max-width: 960px;
    margin: 0 auto;
    display: flex;
    justify-content: center;
    background: transparent;
  }

  .pd-document-image-frame img {
    width: auto;
    max-width: 100%;
    height: auto;
    display: block;
  }

  .pd-document-review > p,
  .pd-document-text-block .pd-body-paragraph,
  .pd-document-course .pd-body-paragraph {
    margin-top: 0;
  }

  .pd-body-paragraph,
  .pd-document-review > p {
    margin: 0;
    max-width: 760px;
    font-family: var(--font-ko);
    font-size: 17px;
    line-height: 1.78;
    letter-spacing: -0.04em;
    color: rgba(21, 21, 21, 0.68);
    word-break: keep-all;
  }

  .pd-document-review-list,
  .pd-document-course-list {
    display: grid;
    gap: 12px;
    margin-top: 24px;
  }

  .pd-document-review-item {
    appearance: none;
    border: 1px solid rgba(21, 21, 21, 0.12);
    background: #ffffff;
    text-align: left;
    padding: 18px 20px;
    cursor: pointer;
  }

  .pd-document-review-item span,
  .pd-document-course-meta,
  .pd-document-course-number {
    font-family: var(--font-en);
    font-size: 11px;
    line-height: 1;
    letter-spacing: 0.14em;
    color: rgba(21, 21, 21, 0.46);
    text-transform: uppercase;
  }

  .pd-document-review-item strong {
    display: block;
    margin-top: 10px;
    font-family: var(--font-ko);
    font-size: 17px;
    line-height: 1.36;
    letter-spacing: -0.045em;
    color: #151515;
    word-break: keep-all;
  }

  .pd-document-course-item {
    display: grid;
    grid-template-columns: 86px 120px minmax(0, 1fr);
    gap: 20px;
    padding: 18px 0;
    border-top: 1px solid rgba(21, 21, 21, 0.1);
  }

  .pd-document-course-meta {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .pd-document-course-copy strong {
    display: block;
    font-family: var(--font-ko);
    font-size: 18px;
    line-height: 1.35;
    letter-spacing: -0.045em;
    color: #151515;
  }

  .pd-document-course-copy p {
    margin: 8px 0 0;
    font-family: var(--font-ko);
    font-size: 14px;
    line-height: 1.7;
    letter-spacing: -0.035em;
    color: rgba(21, 21, 21, 0.62);
    word-break: keep-all;
  }

  .pd-document-excluded-lead {
    margin-top: 34px;
  }

  .pd-body-mini-meta {
    display: inline-grid;
    gap: 10px;
    margin-top: 24px;
    padding: 20px 22px;
    border: 1px solid rgba(21, 21, 21, 0.12);
  }

  .pd-body-mini-meta span,
  .pd-body-notice-buttons small {
    display: block;
    font-family: var(--font-en);
    font-size: 11px;
    line-height: 1;
    letter-spacing: 0.16em;
    color: rgba(21, 21, 21, 0.42);
    text-transform: uppercase;
  }

  .pd-body-mini-meta strong,
  .pd-body-notice-buttons span {
    display: block;
    font-family: var(--font-ko);
    font-size: 16px;
    line-height: 1.35;
    letter-spacing: -0.04em;
    color: #151515;
    word-break: keep-all;
  }

  .pd-body-text-button {
    appearance: none;
    margin-top: 22px;
    border: 0;
    background: transparent;
    padding: 0;
    cursor: pointer;
    font-family: var(--font-ko);
    font-size: 15px;
    line-height: 1;
    letter-spacing: -0.035em;
    color: #151515;
  }

  .pd-body-document-table {
    display: grid;
    gap: 0;
    margin-top: 24px;
    border-top: 1px solid rgba(21, 21, 21, 0.12);
  }

  .pd-body-document-row {
    display: grid;
    grid-template-columns: 54px minmax(0, 1fr);
    gap: 22px;
    padding: 18px 0;
    border-bottom: 1px solid rgba(21, 21, 21, 0.12);
  }

  .pd-body-document-row span {
    font-family: var(--font-en);
    font-size: 12px;
    line-height: 1;
    letter-spacing: 0.14em;
    color: rgba(21, 21, 21, 0.42);
  }

  .pd-body-document-row strong {
    display: block;
    font-family: var(--font-ko);
    font-size: 16px;
    line-height: 1.35;
    letter-spacing: -0.04em;
    color: #151515;
    word-break: keep-all;
  }

  .pd-body-document-row small {
    display: block;
    margin-top: 7px;
    font-family: var(--font-ko);
    font-size: 14px;
    line-height: 1.65;
    letter-spacing: -0.035em;
    color: rgba(21, 21, 21, 0.58);
    word-break: keep-all;
  }

  .pd-body-document-table.is-muted .pd-body-document-row strong {
    color: rgba(21, 21, 21, 0.64);
  }

  .pd-body-notice-buttons {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-top: 24px;
  }

  .pd-body-notice-buttons button {
    appearance: none;
    border: 1px solid rgba(21, 21, 21, 0.12);
    background: #ffffff;
    padding: 18px 20px;
    cursor: pointer;
    text-align: left;
  }

  .pd-meeting-panel {
    display: grid;
    grid-template-columns: 420px minmax(0, 1fr);
    gap: 26px;
    min-height: 360px;
    background: #ffffff;
    border: 1px solid rgba(21, 21, 21, 0.12);
    box-shadow: none;
  }

  .pd-meeting-info {
    padding: 30px;
    box-sizing: border-box;
  }

  .pd-meeting-overline {
    font-family: var(--font-en);
    font-size: 11px;
    line-height: 1;
    letter-spacing: 0.18em;
    color: rgba(21, 21, 21, 0.42);
    text-transform: uppercase;
  }

  .pd-meeting-name {
    margin: 18px 0 0;
    font-family: var(--font-en);
    font-size: 34px;
    line-height: 0.95;
    letter-spacing: -0.055em;
    font-weight: 520;
    color: #151515;
  }

  .pd-meeting-meta-list {
    display: grid;
    gap: 16px;
    margin-top: 36px;
  }

  .pd-meeting-meta-item {
    display: grid;
    gap: 7px;
  }

  .pd-meeting-meta-label {
    display: block;
    margin-bottom: 8px;
    font-family: var(--font-en);
    font-size: 10px;
    line-height: 1;
    letter-spacing: 0.16em;
    color: rgba(21, 21, 21, 0.42);
    text-transform: uppercase;
  }

  .pd-meeting-meta-item strong {
    display: block;
    font-family: var(--font-ko);
    font-size: 15px;
    line-height: 1.5;
    letter-spacing: -0.035em;
    color: #151515;
    word-break: keep-all;
  }

  .pd-meeting-direction {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-top: 34px;
    color: #151515;
    text-decoration: none;
    font-family: var(--font-ko);
    font-size: 14px;
    line-height: 1;
    letter-spacing: -0.035em;
  }

  .pd-meeting-map-frame {
    min-width: 0;
    min-height: 360px;
    background: #f2f2f2;
  }

  .pd-meeting-map-frame iframe {
    width: 100%;
    height: 100%;
    border: 0;
    filter: none;
  }
`;

function MeetingPointBlock({ meetingPoint }: { meetingPoint: MeetingPoint }) {
  return (
    <section className="pd-meeting-panel" aria-label="미팅 장소 및 지도">
      <div className="pd-meeting-info">
        <div className="pd-meeting-overline">MEETING POINT</div>
        <h2 className="pd-meeting-name">{meetingPoint.name}</h2>

        <div className="pd-meeting-meta-list">
          <div className="pd-meeting-meta-item">
            <div>
              <span className="pd-meeting-meta-label">미팅 시간</span>
              <strong>{meetingPoint.time}</strong>
            </div>
          </div>

          <div className="pd-meeting-meta-item">
            <div>
              <span className="pd-meeting-meta-label">주소</span>
              <strong>{meetingPoint.address}</strong>
            </div>
          </div>
        </div>

        <a
          className="pd-meeting-direction"
          href={meetingPoint.directionUrl}
          target="_blank"
          rel="noreferrer"
        >
          길찾기
          <span aria-hidden="true">→</span>
        </a>
      </div>

      <div className="pd-meeting-map-frame">
        <iframe
          title="미팅 장소 Google Map"
          src={meetingPoint.mapUrl}
          loading="lazy"
          referrerPolicy="no-referrer-when-downgrade"
        />
      </div>
    </section>
  );
}

function ProductDocument({
  detailData,
  selectedGuide,
  isDailyTour,
  onOpenReview,
  onOpenNotice,
}: ProductDocumentProps) {
  const [activeTab, setActiveTab] = useState<ProductDetailTab | null>("guide");
  const [isDocumentOpen, setIsDocumentOpen] = useState(true);

  const activeTabContent = useMemo(() => {
    if (!activeTab) {
      return null;
    }

    if (activeTab === "guide") {
      return {
        kicker: "01 · 가이드 정보",
        text: detailData.guide,
      };
    }

    if (activeTab === "review") {
      return {
        kicker: "02 · 리뷰",
        text: detailData.review,
      };
    }

    if (activeTab === "course") {
      return {
        kicker: "03 · 코스 일정",
        text: isDailyTour
          ? "?곗씪由ы닾?대뒗 ?꾩??먯꽌 諛붾줈 ?⑸쪟?섎뒗 寃쎌슦媛 留롪린 ?뚮Ц???щ젰?먯꽌 ?ㅼ젣 媛???좎쭨瑜?癒쇱? ?뺤씤?섎뒗 ?먮쫫??以묒슂?⑸땲?? ?ㅻ뒛 ?댁쟾 ?좎쭨???좏깮?????녿룄濡?泥섎━?⑸땲??"
          : detailData.scheduleIntro,
      };
    }

    if (activeTab === "meeting") {
      return {
        kicker: "03 · 미팅 장소",
        text: detailData.meetingPoint.address,
      };
    }

    if (activeTab === "notice") {
      return {
        kicker: "04 · 예약 안내",
        text: detailData.reservationNotice,
      };
    }

    return {
      kicker: "05 · 포함/불포함",
      text: `${detailData.included}\n${detailData.excluded}`,
    };
  }, [activeTab, detailData, isDailyTour]);

  const noticeButtons = detailData.notices.filter(
    (notice) => notice.title !== "?덉빟 ?덈궡",
  );

  const [contentIndex, contentKicker] = activeTabContent
    ? activeTabContent.kicker.split(" · ")
    : ["", ""];
  return (
    <>
      <style>{STYLE}</style>

      <section className="pd-product-document-strip" aria-label="Product document navigation">
        <div className="pd-product-document-label">
          <strong>PRODUCT DOCUMENT</strong>
        </div>
        <div className="pd-product-document-row" role="tablist" aria-label="?곹뭹 ?곸꽭 臾몄꽌">
          {PRODUCT_DOCUMENT_TABS.map((item) => (
            <button
              key={item.key}
              type="button"
              className={`pd-product-document-item ${activeTab === item.key ? "is-active" : ""}`}
              onClick={() => setActiveTab(item.key)}
              role="tab"
              aria-selected={activeTab === item.key}
            >
              <span>{item.label}</span>
              <strong>{item.index}</strong>
            </button>
          ))}
        </div>
      </section>

      {activeTab && activeTabContent && (
        <section className="pd-product-document-content" aria-label="Product document content">
          <div className="pd-product-document-content-left">
            <div className="pd-document-content-index">{contentIndex}</div>
            <div className="pd-document-content-kicker">{contentKicker}</div>
          </div>

          <div className="pd-product-document-content-main" role="tabpanel">
          {activeTab === "review" && (
            <div className="pd-document-review">
              <p>{detailData.review}</p>

              <div className="pd-document-review-list">
                {detailData.reviews.slice(0, 2).map((review) => (
                  <button
                    key={review.id}
                    type="button"
                    className="pd-document-review-item"
                    onClick={onOpenReview}
                  >
                    <span>
                      {review.nickname} 쨌 {review.writtenAt}
                    </span>
                    <strong>{review.title}</strong>
                  </button>
                ))}
              </div>

              <button type="button" className="pd-body-text-button" onClick={onOpenReview}>
                ?꾩껜 由щ럭 蹂닿린 ??
              </button>
            </div>
          )}

          {activeTab === "course" && (
            <div className="pd-document-course">
              <p className="pd-body-paragraph">{activeTabContent.text}</p>
              <div className="pd-document-course-list">
                {detailData.scheduleDays.map((day) => (
                  <article key={day.day} className="pd-document-course-item">
                    <span className="pd-document-course-number">{day.day}</span>
                    <div className="pd-document-course-meta">
                      <span>{day.city}</span>
                      <span>{day.time}</span>
                    </div>
                    <div className="pd-document-course-copy">
                      <strong>{day.title}</strong>
                      <p>{day.body}</p>
                    </div>
                  </article>
                ))}
              </div>
            </div>
          )}

          {activeTab === "guide" && (
            <div className="pd-document-text-block">
              <p className="pd-body-paragraph">{detailData.guide}</p>
              <div className="pd-body-mini-meta">
                <span>GUIDE</span>
                <strong>{selectedGuide}</strong>
              </div>
            </div>
          )}

          {activeTab === "included" && (
            <div className="pd-document-text-block">
              <p className="pd-body-paragraph">{detailData.included}</p>
              <div className="pd-body-document-table" aria-label="?ы븿 ??ぉ">
                {INCLUDED_ITEMS.map((item, index) => (
                  <div key={item.title} className="pd-body-document-row">
                    <span>{String(index + 1).padStart(2, "0")}</span>
                    <div>
                      <strong>{item.title}</strong>
                      <small>{item.desc}</small>
                    </div>
                  </div>
                ))}
              </div>

              <p className="pd-body-paragraph pd-document-excluded-lead">
                {detailData.excluded}
              </p>
              <div className="pd-body-document-table is-muted" aria-label="遺덊룷????ぉ">
                {EXCLUDED_ITEMS.map((item, index) => (
                  <div key={item.title} className="pd-body-document-row">
                    <span>{String(index + 1).padStart(2, "0")}</span>
                    <div>
                      <strong>{item.title}</strong>
                      <small>{item.desc}</small>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {activeTab === "notice" && (
            <div className="pd-document-text-block">
              <p className="pd-body-paragraph">{detailData.reservationNotice}</p>
              <div className="pd-body-notice-buttons">
                {noticeButtons.map((notice) => (
                  <button key={notice.title} type="button" onClick={() => onOpenNotice(notice)}>
                    <span>{notice.title}</span>
                    <small>VIEW</small>
                  </button>
                ))}
              </div>
            </div>
          )}

          {activeTab === "meeting" && (
            <div className="pd-document-meeting">
              <MeetingPointBlock meetingPoint={detailData.meetingPoint} />
            </div>
          )}
          </div>
        </section>
      )}
    </>
  );
}

export default ProductDocument;
