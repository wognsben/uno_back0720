import CommunityLayout from "../CommunityLayout";
import CommunityList from "../CommunityList";
import CommunityPagination from "../CommunityPagination";
import CommunitySearch from "../CommunitySearch";
import { useCommunityPosts } from "../useCommunityPosts";

export default function NoticePage() {
    const {
        items,
        page,
        search,
        totalPages,
        setPage,
        handleSearch,
    } = useCommunityPosts("notice");

    return (
        <CommunityLayout type="notice">
            <CommunitySearch
                placeholder="공지사항을 검색하세요."
                value={search}
                onSearch={handleSearch}
            />
            <CommunityList type="notice" items={items} />
            <CommunityPagination
                currentPage={page}
                totalPages={totalPages}
                onPageChange={setPage}
            />
        </CommunityLayout>
    );
}
