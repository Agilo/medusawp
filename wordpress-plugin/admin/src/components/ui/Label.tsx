import { twMerge } from "tailwind-merge";

export const UiLabel: React.FC<React.ComponentPropsWithoutRef<"label">> = ({
  children,
  className,
  ...rest
}) => (
  <label
    {...rest}
    className={twMerge(
      "mwp-mb-2 mwp-block mwp-font-semibold mwp-text-grayscale-900 md:mwp-mb-4",
      className,
    )}
  >
    {children}
  </label>
);
