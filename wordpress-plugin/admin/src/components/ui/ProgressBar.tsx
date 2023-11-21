import { twMerge } from "tailwind-merge";

export interface UiProgressBarProps
  extends React.ComponentPropsWithoutRef<"div"> {
  value?: number;
}

export const UiProgressBar: React.FC<UiProgressBarProps> = ({
  value = 0,
  className,
  ...rest
}) => (
  <div
    {...rest}
    className={twMerge(
      "mwp-h-3 mwp-rounded-full mwp-border mwp-border-grayscale-100 mwp-bg-grayscale-50",
      className,
    )}
  >
    <div
      className="-mwp-mt-px mwp-h-3 mwp-rounded-full mwp-bg-blue-300"
      style={{ width: value.toString() + "%" }}
    />
  </div>
);
