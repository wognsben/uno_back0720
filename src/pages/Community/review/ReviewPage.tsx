import CommunityLayout from "../CommunityLayout";
import CommunityList from "../CommunityList";
import CommunityPagination from "../CommunityPagination";
import CommunitySearch from "../CommunitySearch";
import { useCommunityPosts } from "../useCommunityPosts";

export default function ReviewPage() {
    const {
        items,
        page,
        search,
        totalPages,
        setPage,
        handleSearch,
    } = useCommunityPosts("review");

    return (
        <CommunityLayout type="review">
            <CommunitySearch
                placeholder="여행후기를 검색하세요."
                value={search}
                onSearch={handleSearch}
            />
            <CommunityList type="review" items={items} />
            <CommunityPagination
                currentPage={page}
                totalPages={totalPages}
                onPageChange={setPage}
            />
        </CommunityLayout>
    );
}
