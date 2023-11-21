import { twMerge } from "tailwind-merge";

export interface UiCardProps extends React.ComponentPropsWithoutRef<"div"> {
  isDisabled?: boolean;
}

export const UiCard: React.FC<UiCardProps> = ({
  isDisabled,
  children,
  className,
  ...rest
}) => (
  <div
    {...rest}
    className={twMerge(
      "mwp-rounded-md mwp-border mwp-border-grayscale-100 mwp-px-6 mwp-py-5 md:mwp-px-8 md:mwp-py-6",
      isDisabled && "mwp-pointer-events-none mwp-text-grayscale-200",
      className,
    )}
  >
    {children}
  </div>
);
