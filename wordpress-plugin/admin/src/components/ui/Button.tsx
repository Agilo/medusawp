import * as React from "react";
import { NavLink, NavLinkProps } from "react-router-dom";
import { twMerge } from "tailwind-merge";

export type UiButtonOwnProps = {
  variant?: "fill" | "outline";
  size?: "sm" | "md";
  isDisabled?: boolean;
};

const buttonBaseStyles = (
  variant: Exclude<UiButtonOwnProps["variant"], undefined>,
  size: Exclude<UiButtonOwnProps["size"], undefined>,
  isDisabled: UiButtonOwnProps["isDisabled"],
) => {
  return [
    "mwp-inline-block mwp-text-center mwp-rounded-full",
    variant === "fill" &&
      "mwp-transition-colors mwp-text-grayscale-white mwp-outline-none",
    variant === "fill" &&
      !isDisabled &&
      "mwp-bg-grayscale-900 hover:mwp-bg-grayscale-1000",
    variant === "fill" && isDisabled && "mwp-bg-grayscale-100",
    variant === "outline" &&
      "mwp-border mwp-transition-colors hover:mwp-border-blue-200 hover:mwp-bg-blue-200",
    variant === "outline" &&
      !isDisabled &&
      "mwp-border-grayscale-300 mwp-text-grayscale-900",
    variant === "outline" &&
      isDisabled &&
      "mwp-border-grayscale-100 mwp-text-grayscale-200",
    isDisabled && "mwp-cursor-not-allowed",
    size === "sm" && "mwp-leading-8 mwp-px-3 md:mwp-px-6 mwp-text-xs",
    size === "md" && "mwp-leading-10 mwp-px-7 md:mwp-leading-12 md:mwp-px-9",
  ];
};

export const UiButton = React.forwardRef<
  HTMLButtonElement,
  UiButtonOwnProps & React.ComponentPropsWithoutRef<"button">
>(({ variant = "fill", size = "md", isDisabled, className, ...rest }, ref) => (
  <button
    {...rest}
    disabled={isDisabled}
    className={twMerge(buttonBaseStyles(variant, size, isDisabled), className)}
    ref={ref}
  />
));

export const UiButtonLink = React.forwardRef<
  HTMLAnchorElement,
  UiButtonOwnProps & React.ComponentPropsWithoutRef<"a">
>(({ variant = "fill", size = "md", isDisabled, className, ...rest }, ref) => (
  <a
    {...rest}
    className={twMerge(buttonBaseStyles(variant, size, isDisabled), className)}
    ref={ref}
  />
));

export const UiButtonNavLink = React.forwardRef<
  HTMLAnchorElement,
  UiButtonOwnProps & Omit<NavLinkProps, "className"> & { className?: string }
>(
  (
    { variant = "fill", size = "md", isDisabled, className, to, ...rest },
    ref,
  ) => (
    <NavLink
      {...rest}
      to={to}
      className={twMerge(
        buttonBaseStyles(variant, size, isDisabled),
        className,
      )}
      ref={ref}
    />
  ),
);
