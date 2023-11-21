import * as React from "react";
import {
  AlertDialogContentProps,
  AlertDialogOverlayProps,
} from "@radix-ui/react-alert-dialog";
import { twMerge } from "tailwind-merge";

export const UiAlertDialogOverlay = React.forwardRef<
  HTMLDivElement,
  AlertDialogOverlayProps
>(({ className, ...rest }, ref) => (
  <div
    {...rest}
    className={twMerge(
      "mwp-fixed mwp-left-0 mwp-top-0 mwp-z-alert-dialog mwp-h-full mwp-w-full mwp-bg-grayscale-900 mwp-opacity-20",
      className,
    )}
    ref={ref}
  />
));

export const UiAlertDialogContent = React.forwardRef<
  HTMLDivElement,
  AlertDialogContentProps
>(({ className, children, ...rest }, ref) => (
  <div
    {...rest}
    className={twMerge(
      "mwp-fixed mwp-left-1/2 mwp-top-1/2 mwp-z-alert-dialog mwp-max-w-120 -mwp-translate-x-1/2 -mwp-translate-y-1/2 mwp-rounded-md mwp-bg-grayscale-white mwp-px-10 mwp-py-12 mwp-shadow-md",
      className,
    )}
    ref={ref}
  >
    {children}
  </div>
));
