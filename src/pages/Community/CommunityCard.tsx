import type { CommunityCardProps } from "./community.types";

export default function CommunityCard({ item }: CommunityCardProps) {
    const isReview = item.type === "review";
    const isEvent = item.type === "event";
    const isNotice = item.type === "notice";
    const isInquiry = item.type === "inquiry";

    const handleNavigate = (event: React.MouseEvent<HTMLAnchorElement>) => {
        event.preventDefault();

        window.history.pushState({}, "", item.href);
        window.dispatchEvent(new Event("unotravel:navigate"));
    };

    return (
        <article className={`community-card community-card--${item.type}`}>
            <a
                href={item.href}
                className="community-card-link"
                onClick={handleNavigate}
            >
                {item.thumbnail && (
                    <div
                        className="community-card-thumbnail"
                        style={{ backgroundImage: `url(${item.thumbnail})` }}
                        aria-hidden="true"
                    />
                )}

                <div className="community-card-body">
                    <div className="community-card-meta">
                        <span className="community-card-type">
                            {isReview && "REVIEW"}
                            {isNotice && "NOTICE"}
                            {isEvent && "EVENT"}
                            {isInquiry && "Q&A"}
                        </span>

                        {item.isPinned && (
                            <span className="community-card-badge">PINNED</span>
                        )}

                        {item.isNew && (
                            <span className="community-card-badge">NEW</span>
                        )}

                        {item.status && (
                            <span className={`community-card-status is-${item.status}`}>
                                {item.status === "active" && "진행중"}
                                {item.status === "closed" && "종료"}
                                {item.status === "scheduled" && "예정"}
                                {item.status === "default" && "안내"}
                            </span>
                        )}
                    </div>

                    <h2 className="community-card-title">{item.title}</h2>

                    {item.excerpt && (
                        <p className="community-card-excerpt">{item.excerpt}</p>
                    )}

                    <div className="community-card-info">
                        {item.author && <span>{item.author}</span>}
                        <span>{item.date}</span>
                        {typeof item.views === "number" && <span>조회 {item.views}</span>}
                    </div>

                    {isEvent && item.startDate && item.endDate && (
                        <div className="community-card-period">
                            {item.startDate} — {item.endDate}
                        </div>
                    )}
                </div>
            </a>
        </article>
    );
}
