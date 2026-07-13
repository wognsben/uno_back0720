import type { CommunityPaginationProps } from "./community.types";

export default function CommunityPagination({
    currentPage,
    totalPages,
    onPageChange,
}: CommunityPaginationProps) {
    if (totalPages <= 1) {
        return null;
    }

    const maxPagesToShow = 7;
    const halfWindow = Math.floor(maxPagesToShow / 2);
    const startPage = Math.max(
        1,
        Math.min(currentPage - halfWindow, totalPages - maxPagesToShow + 1),
    );
    const endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
    const visiblePages = Array.from(
        { length: endPage - startPage + 1 },
        (_, index) => startPage + index,
    );

    const handlePageChange = (page: number) => {
        if (page < 1 || page > totalPages || page === currentPage) return;
        onPageChange?.(page);
    };

    return (
        <nav className="community-pagination" aria-label="Community Pagination">
            <button
                className="community-pagination-button"
                type="button"
                disabled={currentPage <= 1}
                onClick={() => handlePageChange(currentPage - 1)}
            >
                이전
            </button>

            <div className="community-pagination-numbers">
                {startPage > 1 && (
                    <button
                        className="community-pagination-number"
                        type="button"
                        onClick={() => handlePageChange(1)}
                    >
                        1
                    </button>
                )}
                {startPage > 2 && <span className="community-pagination-number">...</span>}
                {visiblePages.map((page) => (
                    <button
                        key={page}
                        className={`community-pagination-number ${
                            currentPage === page ? "is-active" : ""
                        }`}
                        type="button"
                        aria-current={currentPage === page ? "page" : undefined}
                        onClick={() => handlePageChange(page)}
                    >
                        {page}
                    </button>
                ))}
                {endPage < totalPages - 1 && <span className="community-pagination-number">...</span>}
                {endPage < totalPages && (
                    <button
                        className="community-pagination-number"
                        type="button"
                        onClick={() => handlePageChange(totalPages)}
                    >
                        {totalPages}
                    </button>
                )}
            </div>

            <button
                className="community-pagination-button"
                type="button"
                disabled={currentPage >= totalPages}
                onClick={() => handlePageChange(currentPage + 1)}
            >
                다음
            </button>
        </nav>
    );
}
