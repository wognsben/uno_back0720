import type { CommunityDetailProps } from "./community.types";

export default function CommunityDetail({
    item,
}: CommunityDetailProps) {
    const isEvent = item.type === "event";
    const listHref = `/community/${item.type}`;

    const handleBackNavigate = (event: React.MouseEvent<HTMLAnchorElement>) => {
        event.preventDefault();

        window.history.pushState({}, "", listHref);
        window.dispatchEvent(new Event("unotravel:navigate"));
    };

    return (
        <article className={`community-detail community-detail--${item.type}`}>
            <header className="community-detail-header">
                <div className="community-detail-meta">
                    <span className="community-detail-type">
                        {item.type.toUpperCase()}
                    </span>

                    {item.isPinned && (
                        <span className="community-detail-badge">PINNED</span>
                    )}

                    {item.isNew && (
                        <span className="community-detail-badge">NEW</span>
                    )}
                </div>

                <h1 className="community-detail-title">{item.title}</h1>

                <div className="community-detail-info">
                    {item.author && <span>{item.author}</span>}
                    <span>{item.date}</span>
                    {typeof item.views === "number" && <span>조회 {item.views}</span>}
                </div>
            </header>

            {item.thumbnail && (
                <div
                    className="community-detail-thumbnail"
                    style={{ backgroundImage: `url(${item.thumbnail})` }}
                    aria-hidden="true"
                />
            )}

            {isEvent && item.startDate && item.endDate && (
                <div className="community-detail-period">
                    <span>EVENT PERIOD</span>
                    <strong>
                        {item.startDate} — {item.endDate}
                    </strong>
                </div>
            )}

            <div className="community-detail-content">
                {item.contentHtml ? (
                    <div dangerouslySetInnerHTML={{ __html: item.contentHtml }} />
                ) : (
                    <p>{item.contentText ?? item.excerpt}</p>
                )}
            </div>

            <footer className="community-detail-footer">
                <a
                    href={listHref}
                    className="community-detail-back"
                    onClick={handleBackNavigate}
                >
                    목록으로
                </a>
            </footer>
        </article>
    );
}
