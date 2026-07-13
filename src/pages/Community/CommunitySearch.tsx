import { useEffect, useState, type FormEvent } from "react";

import type { CommunitySearchProps } from "./community.types";

export default function CommunitySearch({
    placeholder = "검색어를 입력하세요.",
    value = "",
    onSearch,
}: CommunitySearchProps) {
    const [inputValue, setInputValue] = useState(value);

    useEffect(() => {
        setInputValue(value);
    }, [value]);

    const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        onSearch?.(inputValue.trim());
    };

    return (
        <section className="community-search" aria-label="Community Search">
            <form className="community-search-inner" onSubmit={handleSubmit}>
                <label className="community-search-label" htmlFor="community-search-input">
                    SEARCH
                </label>

                <input
                    id="community-search-input"
                    className="community-search-input"
                    type="search"
                    value={inputValue}
                    onChange={(event) => setInputValue(event.target.value)}
                    placeholder={placeholder}
                />

                <button className="community-search-button" type="submit">
                    검색
                </button>
            </form>
        </section>
    );
}
