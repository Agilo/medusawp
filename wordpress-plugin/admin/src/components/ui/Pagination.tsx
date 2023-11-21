import { Link } from "react-router-dom";
import { twMerge } from "tailwind-merge";

export const UiPagination: React.FC<React.ComponentPropsWithoutRef<"div">> = ({
  children,
  className,
  ...rest
}) => (
  <div
    {...rest}
    className={twMerge("mwp-inline-flex mwp-gap-2 md:mwp-gap-4", className)}
  >
    {children}
  </div>
);

export interface UiPaginationItemOwnProps {
  isActive?: boolean;
  isDisabled?: boolean;
  navigation?: "previous" | "next";
}

export const UiPaginationEllipsis: React.FC<
  React.ComponentPropsWithoutRef<"div">
> = ({ className, ...rest }) => (
  <div
    {...rest}
    className={twMerge(
      "mwp-pointer-events-none mwp-h-8 mwp-w-8 mwp-rounded-full mwp-text-xs mwp-transition-colors md:mwp-h-9 md:mwp-w-9 md:mwp-text-sm",
      className,
    )}
  >
    <div className="mwp-flex mwp-h-full mwp-w-full mwp-items-center mwp-justify-center">
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width="24"
        height="24"
        fill="none"
      >
        <path
          fill="#262829"
          fillRule="evenodd"
          d="M5 10.25a1.75 1.75 0 1 0 0 3.5 1.75 1.75 0 0 0 0-3.5Zm7 0a1.75 1.75 0 1 0 0 3.5 1.75 1.75 0 0 0 0-3.5Zm7 0a1.75 1.75 0 1 0 0 3.5 1.75 1.75 0 0 0 0-3.5Z"
          clipRule="evenodd"
        />
      </svg>
    </div>
  </div>
);

export const UiPaginationItem: React.FC<
  React.ComponentPropsWithoutRef<typeof Link> & UiPaginationItemOwnProps
> = ({ isActive, isDisabled, navigation, children, className, ...rest }) => (
  <Link
    {...rest}
    className={twMerge(
      "mwp-h-8 mwp-w-8 mwp-rounded-full mwp-text-xs mwp-leading-8 mwp-transition-colors md:mwp-h-9 md:mwp-w-9 md:mwp-text-sm md:mwp-leading-9",
      !isActive &&
        !isDisabled &&
        "mwp-text-grayscale-900 hover:mwp-bg-grayscale-100",
      isActive && "mwp-bg-grayscale-900 mwp-text-grayscale-white",
      isDisabled && "mwp-cursor-not-allowed mwp-text-grayscale-200",
      className,
    )}
  >
    {navigation === "previous" ? (
      <div className="mwp-flex mwp-h-full mwp-w-full mwp-items-center mwp-justify-center">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          fill="none"
        >
          <path
            fill={isDisabled ? "#EAEEEE" : "#0D0D0D"}
            fillRule="evenodd"
            d="M15.53 5.47a.75.75 0 0 1 0 1.06L10.06 12l5.47 5.47a.75.75 0 1 1-1.06 1.06l-6-6a.75.75 0 0 1 0-1.06l6-6a.75.75 0 0 1 1.06 0Z"
            clipRule="evenodd"
          />
        </svg>
      </div>
    ) : navigation === "next" ? (
      <div className="mwp-flex mwp-h-full mwp-w-full mwp-items-center mwp-justify-center">
        <svg
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            fillRule="evenodd"
            clipRule="evenodd"
            d="M8.46967 5.46967C8.76256 5.17678 9.23744 5.17678 9.53033 5.46967L15.5303 11.4697C15.8232 11.7626 15.8232 12.2374 15.5303 12.5303L9.53033 18.5303C9.23744 18.8232 8.76256 18.8232 8.46967 18.5303C8.17678 18.2374 8.17678 17.7626 8.46967 17.4697L13.9393 12L8.46967 6.53033C8.17678 6.23744 8.17678 5.76256 8.46967 5.46967Z"
            fill={isDisabled ? "#EAEEEE" : "#0D0D0D"}
          />
        </svg>
      </div>
    ) : (
      children
    )}
  </Link>
);
