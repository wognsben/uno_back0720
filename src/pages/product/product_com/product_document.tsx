import { useMemo, useState } from "react";

export type ProductDetailTab =
  | "review"
  | "guide"
  | "tourInfo"
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

export type ProductDocumentGuide = {
  id: number | string;
  name: string;
  bodyText?: string;
  imageUrl?: string;
  imageAlt?: string;
};

export type ProductDocumentData = {
  productType: "semi" | "daily";
  guide: string;
  guides?: ProductDocumentGuide[];
  included: string;
  excluded: string;
  review: string;
  reviews: ReviewItem[];
  tourDay?: string;
  tourTime?: string;
  priceDescription?: string;
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
};

const PRODUCT_DOCUMENT_TABS: Array<{
  key: ProductDetailTab;
  label: string;
  index: string;
}> = [
  { key: "guide", label: "가이드 정보", index: "01" },
  { key: "review", label: "리뷰", index: "02" },
  { key: "tourInfo", label: "투어 정보", index: "03" },
  { key: "meeting", label: "미팅 장소", index: "04" },
  { key: "notice", label: "예약 안내", index: "05" },
  { key: "included", label: "포함/불포함", index: "06" },
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
    justify-content: center;
    font-family: var(--font-en);
    font-size: 18px;
    line-height: 1;
    letter-spacing: 0.16em;
    color: #151515;
    text-align: center;
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
    align-items: center;
    justify-content: space-between;
    padding: 20px 22px;
    color: rgba(21, 21, 21, 0.44);
    text-align: center;
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
    width: min(1600px, calc(100vw - 100px));
    margin: 0 auto 84px;
    padding: 38px 0 54px;
    border-top: 1px solid rgba(21, 21, 21, 0.14);
    border-bottom: 1px solid rgba(21, 21, 21, 0.14);
    display: grid;
    grid-template-columns: 220px minmax(0, 1fr);
    gap: 34px;
    box-sizing: border-box;
  }

  .pd-product-document-content-left {
    border-right: 1px solid rgba(21, 21, 21, 0.08);
    padding-right: 24px;
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
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    align-content: start;
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
    max-width: none;
    font-family: var(--font-ko);
    font-size: 17px;
    line-height: 1.78;
    letter-spacing: -0.04em;
    color: rgba(21, 21, 21, 0.68);
    white-space: pre-wrap;
    word-break: keep-all;
  }

  .pd-document-review-list,
  .pd-document-course-list {
    display: grid;
    gap: 12px;
    margin-top: 24px;
    width: 100%;
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
    grid-template-columns: 86px minmax(0, 1fr);
    gap: 18px 22px;
    padding: 20px 0;
    border-top: 1px solid rgba(21, 21, 21, 0.1);
  }

  .pd-document-course-meta {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .pd-document-course-number {
    align-self: start;
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

  .pd-document-info-stack {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
  }

  .pd-document-info-stack.is-tour-info {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .pd-document-info-card {
    min-width: 0;
    border: 1px solid rgba(21, 21, 21, 0.12);
    background: #ffffff;
    padding: 24px 24px 28px;
  }

  .pd-document-info-card h3 {
    margin: 0 0 16px;
    font-family: var(--font-ko);
    font-size: 20px;
    line-height: 1.35;
    letter-spacing: -0.045em;
    color: #151515;
  }

  .pd-document-info-card .pd-body-paragraph {
    font-size: 16px;
    line-height: 1.82;
    color: rgba(21, 21, 21, 0.72);
  }

  .pd-body-mini-meta {
    display: grid;
    gap: 10px;
    margin-top: 24px;
    padding: 20px 22px;
    border: 1px solid rgba(21, 21, 21, 0.12);
  }

  .pd-body-mini-meta span,
  .pd-body-notice-buttons span {
    display: block;
    font-family: var(--font-en);
    font-size: 11px;
    line-height: 1;
    letter-spacing: 0.16em;
    color: rgba(21, 21, 21, 0.42);
    text-transform: uppercase;
  }

  .pd-body-mini-meta strong,
  .pd-body-notice-buttons small {
    display: block;
    font-family: var(--font-ko);
    font-size: 16px;
    line-height: 1.72;
    letter-spacing: -0.04em;
    color: #151515;
    white-space: pre-wrap;
    word-break: keep-all;
  }

  .pd-guide-list {
    display: grid;
    gap: 14px;
    margin-top: 24px;
  }

  .pd-guide-card {
    display: grid;
    grid-template-columns: 112px minmax(0, 1fr);
    gap: 18px;
    align-items: start;
    padding: 20px 22px;
    border: 1px solid rgba(21, 21, 21, 0.12);
    background: #ffffff;
  }

  .pd-guide-card.is-text-only {
    grid-template-columns: 1fr;
  }

  .pd-guide-photo {
    width: 112px;
    aspect-ratio: 1 / 1.2;
    overflow: hidden;
    background: rgba(21, 21, 21, 0.06);
  }

  .pd-guide-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  .pd-guide-card strong {
    display: block;
    font-family: var(--font-ko);
    font-size: 18px;
    line-height: 1.35;
    letter-spacing: -0.045em;
    color: #151515;
  }

  .pd-guide-card p {
    margin: 10px 0 0;
    font-family: var(--font-ko);
    font-size: 14px;
    line-height: 1.75;
    letter-spacing: -0.035em;
    color: rgba(21, 21, 21, 0.64);
    white-space: pre-line;
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
    width: 100%;
  }

  .pd-body-document-row {
    display: grid;
    grid-template-columns: 54px minmax(0, 1fr);
    gap: 18px;
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

  .pd-body-notice-card {
    border: 1px solid rgba(21, 21, 21, 0.12);
    background: #ffffff;
    padding: 22px 24px;
    text-align: left;
  }

  .pd-meeting-panel {
    display: grid;
    grid-template-columns: minmax(280px, 0.38fr) minmax(0, 1fr);
    gap: 0;
    min-height: 0;
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

  @media (max-width: 1024px) {
    .pd-product-document-content {
      width: calc(100vw - 40px);
      grid-template-columns: 1fr;
      gap: 24px;
      margin-bottom: 64px;
      padding: 30px 0 42px;
    }

    .pd-product-document-content-left {
      border-right: 0;
      border-bottom: 1px solid rgba(21, 21, 21, 0.08);
      padding: 0 0 22px;
    }

    .pd-document-content-index {
      font-size: 54px;
    }

    .pd-document-content-kicker {
      margin-top: 12px;
    }

    .pd-document-course-item,
    .pd-body-document-row,
    .pd-document-info-stack,
    .pd-document-info-stack.is-tour-info,
    .pd-body-notice-buttons,
    .pd-meeting-panel {
      grid-template-columns: 1fr;
    }

    .pd-document-course-meta {
      flex-direction: row;
      flex-wrap: wrap;
    }

    .pd-meeting-map-frame {
      min-height: 300px;
    }
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
  onOpenReview,
}: ProductDocumentProps) {
  const [activeTab, setActiveTab] = useState<ProductDetailTab | null>(null);
  const [isDocumentOpen, setIsDocumentOpen] = useState(false);

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

    if (activeTab === "meeting") {
      return {
        kicker: "04 · 미팅 장소",
        text: detailData.meetingPoint.address,
      };
    }

    if (activeTab === "tourInfo") {
      return {
        kicker: "03 · 투어 정보",
        text: [detailData.tourDay, detailData.tourTime, detailData.priceDescription]
          .filter(Boolean)
          .join("\n"),
      };
    }

    if (activeTab === "notice") {
      return {
        kicker: "05 · 예약 안내",
        text: detailData.reservationNotice,
      };
    }

    return {
      kicker: "06 · 포함/불포함",
      text: `${detailData.included}\n${detailData.excluded}`,
    };
  }, [activeTab, detailData]);

  const noticeButtons = detailData.notices.filter(
    (notice) => notice.title !== "예약 안내",
  );

  const [contentIndex, contentKicker] = activeTabContent
    ? activeTabContent.kicker.split(" · ")
    : ["", ""];
  return (
    <>
      <style>{STYLE}</style>

      <section className="pd-product-document-strip" aria-label="상품 상세 문서 내비게이션">
        <div className="pd-product-document-label">
          <strong>운영 안내 및 설명</strong>
        </div>
        <div className="pd-product-document-row" role="tablist" aria-label="상품 상세 문서">
          {PRODUCT_DOCUMENT_TABS.map((item) => (
            <button
              key={item.key}
              type="button"
              className={`pd-product-document-item ${activeTab === item.key && isDocumentOpen ? "is-active" : ""}`}
              onClick={() => {
                if (activeTab === item.key && isDocumentOpen) {
                  setIsDocumentOpen(false);
                  return;
                }

                setActiveTab(item.key);
                setIsDocumentOpen(true);
              }}
              role="tab"
              aria-expanded={activeTab === item.key && isDocumentOpen}
              aria-selected={activeTab === item.key && isDocumentOpen}
            >
              <span>{item.label}</span>
              <strong>{item.index}</strong>
            </button>
          ))}
        </div>
      </section>

      {isDocumentOpen && activeTab && activeTabContent && (
        <section className="pd-product-document-content" aria-label="상품 상세 문서 내용">
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
                      {review.nickname} · {review.writtenAt}
                    </span>
                    <strong>{review.title}</strong>
                  </button>
                ))}
              </div>

              <button type="button" className="pd-body-text-button" onClick={onOpenReview}>
                전체 리뷰 보기 →
              </button>
            </div>
          )}

          {activeTab === "guide" && (
            <div className="pd-document-text-block">
              <p className="pd-body-paragraph">{detailData.guide}</p>
              {detailData.guides && detailData.guides.length > 0 && (
                <div className="pd-guide-list" aria-label="담당 가이드 소개">
                  {detailData.guides.map((guide) => (
                    <article
                      key={guide.id}
                      className={`pd-guide-card ${guide.imageUrl ? "" : "is-text-only"}`}
                    >
                      {guide.imageUrl ? (
                        <div className="pd-guide-photo">
                          <img
                            src={guide.imageUrl}
                            alt={guide.imageAlt || guide.name}
                            loading="lazy"
                          />
                        </div>
                      ) : null}
                      <div>
                        <strong>{guide.name}</strong>
                        {guide.bodyText ? <p>{guide.bodyText}</p> : null}
                      </div>
                    </article>
                  ))}
                </div>
              )}
              <div className="pd-body-mini-meta">
                <span>GUIDE</span>
                <strong>{selectedGuide}</strong>
              </div>
            </div>
          )}

          {activeTab === "included" && (
            <div className="pd-document-text-block">
              <div className="pd-document-info-stack" aria-label="포함 및 불포함 안내">
                <article className="pd-document-info-card">
                  <h3>포함사항</h3>
                  <p className="pd-body-paragraph">{detailData.included}</p>
                </article>

                <article className="pd-document-info-card">
                  <h3>불포함사항</h3>
                  <p className="pd-body-paragraph">{detailData.excluded}</p>
                </article>
              </div>
            </div>
          )}

          {activeTab === "tourInfo" && (
            <div className="pd-document-text-block">
              <div className="pd-document-info-stack is-tour-info" aria-label="투어 정보">
                <article className="pd-document-info-card">
                  <h3>투어 요일</h3>
                  <p className="pd-body-paragraph">{detailData.tourDay || "-"}</p>
                </article>

                <article className="pd-document-info-card">
                  <h3>투어 시간</h3>
                  <p className="pd-body-paragraph">{detailData.tourTime || "-"}</p>
                </article>

                <article className="pd-document-info-card">
                  <h3>투어 요금 설명</h3>
                  <p className="pd-body-paragraph">{detailData.priceDescription || "-"}</p>
                </article>
              </div>
            </div>
          )}

          {activeTab === "notice" && (
            <div className="pd-document-text-block">
              <p className="pd-body-paragraph">{detailData.reservationNotice}</p>
              <div className="pd-body-notice-buttons">
                {noticeButtons.map((notice) => (
                  <article key={notice.title} className="pd-body-notice-card">
                    <span>{notice.title}</span>
                    <small>{notice.body}</small>
                  </article>
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
