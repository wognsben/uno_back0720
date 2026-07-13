import { useCallback, useEffect, useState } from "react";

import {
  getCommunityPosts,
  type CommunityBoardPost,
  type CommunityBoardType,
} from "../../api/reservationApi";

const DEFAULT_PER_PAGE = 10;

export function useCommunityPosts(type: CommunityBoardType) {
  const [items, setItems] = useState<CommunityBoardPost[]>([]);
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState("");
  const [totalPages, setTotalPages] = useState(1);
  const [reloadToken, setReloadToken] = useState(0);

  useEffect(() => {
    let isCancelled = false;

    getCommunityPosts({
      type,
      page,
      perPage: DEFAULT_PER_PAGE,
      search,
    })
      .then((response) => {
        if (isCancelled) return;
        setItems(response.items);
        setTotalPages(response.pagination.totalPages);
      })
      .catch(() => {
        if (isCancelled) return;
        setItems([]);
        setTotalPages(1);
      });

    return () => {
      isCancelled = true;
    };
  }, [type, page, search, reloadToken]);

  const handleSearch = useCallback((value: string) => {
    setSearch(value);
    setPage(1);
  }, []);

  const reload = useCallback(() => {
    setReloadToken((value) => value + 1);
  }, []);

  return {
    items,
    page,
    search,
    totalPages,
    setPage,
    setItems,
    setTotalPages,
    handleSearch,
    reload,
  };
}
