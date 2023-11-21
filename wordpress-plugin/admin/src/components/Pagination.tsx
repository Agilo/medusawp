import * as React from "react";
import { useSearchParams } from "react-router-dom";
import range from "lodash/range";
import {
  UiPagination,
  UiPaginationEllipsis,
  UiPaginationItem,
} from "./ui/Pagination";

const mergeSearchParams = (
  oldSearchParams: URLSearchParams,
  newParams: Record<string, string | number>,
) => {
  const searchParams = new URLSearchParams(oldSearchParams);

  for (const [key, value] of Object.entries(newParams)) {
    searchParams.set(key, String(value));
  }
  return searchParams;
};

interface PaginateOptions {
  page: number;
  lastPage: number;
  rangeSize?: number;
}

const paginate = ({
  page,
  lastPage,
  rangeSize = 2,
}: PaginateOptions): Array<number | "..."> => {
  if (lastPage < 2) {
    return [1];
  }

  const midPages = range(
    Math.max(2, page - rangeSize),
    Math.min(lastPage, page + rangeSize + 1),
  );

  return [
    1,
    ...(midPages[0] !== 2 ? ["..." as const] : []),
    ...midPages,
    ...(midPages[midPages.length - 1] !== lastPage - 1 ? ["..." as const] : []),
    lastPage,
  ];
};

export const Pagination: React.FC<{
  queryKey?: string;
  lastPage: number;
}> = ({ queryKey = "page", lastPage }) => {
  const [searchParams] = useSearchParams();
  const page = Number(searchParams.get(queryKey)) || 1;

  if (lastPage < 2) {
    return null;
  }

  const pages = paginate({
    page,
    lastPage,
  });

  return (
    <UiPagination>
      <UiPaginationItem
        navigation="previous"
        isDisabled={page > 1}
        to={
          page > 1
            ? {
                search: mergeSearchParams(searchParams, {
                  [queryKey]: page - 1,
                }).toString(),
              }
            : ""
        }
      />
      {pages.map((p, index) =>
        p === "..." ? (
          <UiPaginationEllipsis key={`ellipsis_${index}`} />
        ) : (
          <UiPaginationItem
            key={p}
            isActive={page === p}
            to={{
              search: mergeSearchParams(searchParams, {
                [queryKey]: p,
              }).toString(),
            }}
          >
            {p}
          </UiPaginationItem>
        ),
      )}
      <UiPaginationItem
        navigation="next"
        isDisabled={page === lastPage}
        to={
          page < lastPage
            ? {
                search: mergeSearchParams(searchParams, {
                  [queryKey]: page + 1,
                }).toString(),
              }
            : ""
        }
      />
    </UiPagination>
  );
};
