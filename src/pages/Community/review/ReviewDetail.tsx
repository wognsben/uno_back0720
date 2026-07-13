import { useEffect, useState } from "react";

import {
    getCommunityPostDetail,
    type CommunityBoardPost,
} from "../../../api/reservationApi";
import CommunityDetail from "../CommunityDetail";
import CommunityEmpty from "../CommunityEmpty";
import CommunityLayout from "../CommunityLayout";

type Props = {
    id: string;
};

export default function ReviewDetail({ id }: Props) {
    const [item, setItem] = useState<CommunityBoardPost | null>(null);

    useEffect(() => {
        let isCancelled = false;

        getCommunityPostDetail("review", id)
            .then((response) => {
                if (isCancelled) return;
                setItem(response.item);
            })
            .catch(() => {
                if (isCancelled) return;
                setItem(null);
            });

        return () => {
            isCancelled = true;
        };
    }, [id]);

    return (
        <CommunityLayout type="review">
            {item ? <CommunityDetail item={item} /> : <CommunityEmpty />}
        </CommunityLayout>
    );
}
