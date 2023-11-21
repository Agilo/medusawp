import * as React from "react";
import { twMerge } from "tailwind-merge";
import CheckboxCheckmark from "../../assets/checkbox-checkmark.svg";
import { createCounter } from "utils/counter";

export interface UiCheckboxProps
  extends React.ComponentPropsWithoutRef<"input"> {
  label?: string;
  labelProps?: Omit<React.ComponentPropsWithoutRef<"label">, "htmlFor">;
}

const getCheckboxId = createCounter("mwp-checkbox");

export const UiCheckbox = React.forwardRef<HTMLInputElement, UiCheckboxProps>(
  ({ label, labelProps, disabled, className, style, ...rest }, ref) => {
    const id = React.useMemo(() => rest.id ?? getCheckboxId(), [rest.id]);

    return (
      <div className="mwp-flex mwp-gap-2">
        <input
          {...rest}
          id={id}
          type="checkbox"
          disabled={disabled}
          style={{
            ...style,
            "--mwp-checkbox-icon": `url(${CheckboxCheckmark})`,
            top: "3px",
          }}
          className={twMerge(
            "mwp-relative mwp-m-0 mwp-h-4 mwp-w-4 mwp-rounded-xxs mwp-border mwp-shadow-none before:-mwp-ml-px before:-mwp-mt-px before:mwp-h-4 before:mwp-w-4 before:mwp-bg-[image:var(--mwp-checkbox-icon)]",
            !disabled &&
              "mwp-border-grayscale-600 checked:mwp-border-grayscale-900 checked:mwp-bg-grayscale-900",
            disabled &&
              "mwp-cursor-not-allowed mwp-border-grayscale-200 mwp-bg-grayscale-200 checked:mwp-border-grayscale-200 checked:mwp-bg-grayscale-200",
            className,
          )}
          ref={ref}
        />
        {label && (
          <label
            {...labelProps}
            className={twMerge(
              disabled ? "mwp-cursor-not-allowed" : undefined,
              labelProps?.className,
            )}
            htmlFor={id}
          >
            {label}
          </label>
        )}
      </div>
    );
  },
);
