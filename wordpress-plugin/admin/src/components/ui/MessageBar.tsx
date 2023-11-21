import { twMerge, twJoin } from "tailwind-merge";

export interface UiMessageBarProps
  extends React.ComponentPropsWithoutRef<"div"> {
  variant?: "success" | "warning" | "error";
  hasIcon?: boolean;
  label?: string;
  message?: string;
  button?: React.ReactNode;
  isClickable?: boolean;
}

export const UiMessageBar: React.FC<UiMessageBarProps> = ({
  variant = "success",
  hasIcon = true,
  label,
  message,
  button,
  isClickable,
  className,
  ...rest
}) => (
  <div
    {...rest}
    className={twMerge(
      "mwp-relative mwp-flex mwp-min-h-14 mwp-flex-wrap mwp-items-center mwp-rounded-sm mwp-border mwp-px-4 mwp-py-3 mwp-transition-colors sm:mwp-flex-nowrap",
      variant === "error" &&
        "mwp-border-transparent mwp-bg-red-50 mwp-text-red-500 data-[state=open]:mwp-border-red-200",
      variant === "warning" &&
        "mwp-border-transparent mwp-bg-orange-50 mwp-text-orange-500 data-[state=open]:mwp-border-orange-200",
      variant === "success" &&
        "mwp-border mwp-border-grayscale-100 mwp-text-grayscale-900 data-[state=open]:mwp-border-grayscale-200",
      isClickable && "mwp-cursor-pointer",
      isClickable && variant === "error" && "hover:mwp-border-red-200",
      isClickable && variant === "warning" && "hover:mwp-border-orange-200",
      isClickable && variant === "success" && "hover:mwp-border-grayscale-200",
      className,
    )}
  >
    {hasIcon && (
      <div
        className={twJoin(
          "mwp-mr-4 mwp-flex mwp-h-9 mwp-w-9 mwp-shrink-0 mwp-items-center mwp-justify-center mwp-rounded-full",
          variant === "error" && "mwp-bg-red-200",
          variant === "warning" && "mwp-bg-orange-200",
          variant === "success" && "mwp-bg-green-200",
        )}
      >
        <div>
          {variant === "error" ? (
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
                d="M12 2.75C6.89137 2.75 2.75 6.89137 2.75 12C2.75 17.1086 6.89137 21.25 12 21.25C17.1086 21.25 21.25 17.1086 21.25 12C21.25 6.89137 17.1086 2.75 12 2.75ZM1.25 12C1.25 6.06294 6.06294 1.25 12 1.25C17.9371 1.25 22.75 6.06294 22.75 12C22.75 17.9371 17.9371 22.75 12 22.75C6.06294 22.75 1.25 17.9371 1.25 12Z"
                fill="#DF4718"
              />
              <path
                fillRule="evenodd"
                clipRule="evenodd"
                d="M15.5303 9.53033C15.8232 9.23744 15.8232 8.76256 15.5303 8.46967C15.2374 8.17678 14.7626 8.17678 14.4697 8.46967L12 10.9393L9.53033 8.46967C9.23744 8.17678 8.76256 8.17678 8.46967 8.46967C8.17678 8.76256 8.17678 9.23744 8.46967 9.53033L10.9393 12L8.46967 14.4697C8.17678 14.7626 8.17678 15.2374 8.46967 15.5303C8.76256 15.8232 9.23744 15.8232 9.53033 15.5303L12 13.0607L14.4697 15.5303C14.7626 15.8232 15.2374 15.8232 15.5303 15.5303C15.8232 15.2374 15.8232 14.7626 15.5303 14.4697L13.0607 12L15.5303 9.53033Z"
                fill="#DF4718"
              />
            </svg>
          ) : variant === "warning" ? (
            <svg
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2"
                stroke="#EE8E36"
                strokeWidth="1.5"
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeDasharray="3 4"
              />
              <path
                d="M12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22"
                stroke="#EE8E36"
                strokeWidth="1.5"
                strokeLinecap="round"
                strokeLinejoin="round"
              />
              <path
                d="M15 9L9 15"
                stroke="#EE8E36"
                strokeWidth="1.5"
                strokeLinecap="round"
                strokeLinejoin="round"
              />
              <path
                d="M9 9L15 15"
                stroke="#EE8E36"
                strokeWidth="1.5"
                strokeLinecap="round"
                strokeLinejoin="round"
              />
            </svg>
          ) : variant === "success" ? (
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
                d="M1.25 12C1.25 6.06279 6.06279 1.25 12 1.25C17.9372 1.25 22.75 6.06279 22.75 12C22.75 17.9372 17.9372 22.75 12 22.75C6.06279 22.75 1.25 17.9372 1.25 12ZM12 2.75C6.89121 2.75 2.75 6.89121 2.75 12C2.75 17.1088 6.89121 21.25 12 21.25C17.1088 21.25 21.25 17.1088 21.25 12C21.25 6.89121 17.1088 2.75 12 2.75Z"
                fill="#13AF89"
              />
              <path
                fillRule="evenodd"
                clipRule="evenodd"
                d="M15.5303 9.46967C15.8232 9.76256 15.8232 10.2374 15.5303 10.5303L11.5303 14.5303C11.2374 14.8232 10.7626 14.8232 10.4697 14.5303L8.46967 12.5303C8.17678 12.2374 8.17678 11.7626 8.46967 11.4697C8.76256 11.1768 9.23744 11.1768 9.53033 11.4697L11 12.9393L14.4697 9.46967C14.7626 9.17678 15.2374 9.17678 15.5303 9.46967Z"
                fill="#13AF89"
              />
            </svg>
          ) : null}
        </div>
      </div>
    )}
    {label && <div className="mwp-mr-4">{label}</div>}
    {message && (
      <div className="mwp-mb-4 mwp-mt-3 mwp-w-full sm:mwp-mb-0 sm:mwp-mr-8 sm:mwp-mt-0 sm:mwp-w-auto">
        {message}
      </div>
    )}
    {button && (
      <div className="mwp-ml-auto mwp-w-full mwp-shrink-0 sm:mwp-w-auto">
        {button}
      </div>
    )}
    {isClickable && (
      <div className="mwp-absolute mwp-right-4 mwp-top-4 mwp-text-grayscale-900 sm:mwp-static sm:mwp-ml-3">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          width="24"
          height="24"
          viewBox="0 0 24 24"
        >
          <path
            fill="currentcolor"
            fill-rule="evenodd"
            d="M12.53 16.53a.75.75 0 0 1-1.06 0l-6-6a.75.75 0 1 1 1.06-1.06L12 14.94l5.47-5.47a.75.75 0 1 1 1.06 1.06l-6 6Z"
            clip-rule="evenodd"
          />
        </svg>
      </div>
    )}
  </div>
);
