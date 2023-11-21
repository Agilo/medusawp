import * as React from "react";
import { NavLink } from "react-router-dom";
import * as Popover from "@radix-ui/react-popover";
import * as AlertDialog from "@radix-ui/react-alert-dialog";
import { twJoin } from "tailwind-merge";

import logo from "assets/logo.svg";
import { useConnectedStore, useStore } from "store/hooks";
import { StoreContextValue } from "store/context";
import { UiLabel } from "./ui/Label";
import { UiInput } from "./ui/Input";
import { UiButton } from "./ui/Button";
import { UiAlertDialogContent, UiAlertDialogOverlay } from "./ui/AlertDialog";

// TODO: loading state
// TODO: error state
// TODO: medusa disconnect failure error
const ConnectionButton: React.FC<{
  connection: Exclude<StoreContextValue["connection"], null>;
}> = ({ connection }) => {
  const mwpRoot = document.getElementById("mwp-root");
  const url = React.useMemo(() => {
    return new URL(connection.url);
  }, [connection.url]);
  const store = useConnectedStore();
  const [connectionModalStep, setConnectionModalStep] = React.useState<
    false | "check" | "success"
  >(false);

  return (
    <Popover.Root>
      <Popover.Trigger asChild>
        <button className="mwp-h-15 mwp-flex mwp-items-center mwp-gap-5 mwp-rounded-sm mwp-border mwp-border-grayscale-100 mwp-pl-6 mwp-pr-4 mwp-text-left mwp-text-grayscale-900">
          <div>
            <div className="mwp-font-semibold">{url.hostname}</div>
            <div className="mwp-text-2xs">Medusa connected</div>
          </div>
          <div className="mwp-h-6 mwp-w-6">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
            >
              <path
                fill="currentcolor"
                fillRule="evenodd"
                d="M12.53 16.53a.75.75 0 0 1-1.06 0l-6-6a.75.75 0 1 1 1.06-1.06L12 14.94l5.47-5.47a.75.75 0 1 1 1.06 1.06l-6 6Z"
                clipRule="evenodd"
              />
            </svg>
          </div>
        </button>
      </Popover.Trigger>
      <Popover.Content
        sideOffset={16}
        align="end"
        className="mwp-z-popover-content md:mwp-w-90 mwp-rounded-md mwp-border mwp-border-solid mwp-border-grayscale-100 mwp-bg-grayscale-white mwp-p-6 mwp-shadow-md"
      >
        <div className="mwp-mb-6">
          <UiLabel htmlFor="url">Medusa URL</UiLabel>
          <UiInput
            name="url"
            id="url"
            type="url"
            value={connection.url}
            disabled
          />
        </div>
        <div className="mwp-mb-10">
          <UiLabel htmlFor="email">Admin email</UiLabel>
          <UiInput
            name="email"
            id="email"
            type="email"
            value={connection.email}
            disabled
          />
        </div>
        <UiButton onClick={() => setConnectionModalStep("check")}>
          Disconnect
        </UiButton>
        <AlertDialog.Root
          open={connectionModalStep === "check"}
          onOpenChange={(open) => {
            setConnectionModalStep((val) => {
              if (val === "success") {
                return val;
              }

              return open ? "check" : false;
            });
          }}
        >
          <AlertDialog.Portal container={mwpRoot}>
            <AlertDialog.Overlay asChild>
              <UiAlertDialogOverlay />
            </AlertDialog.Overlay>
            <AlertDialog.Content asChild>
              <UiAlertDialogContent>
                <div className="mwp-mb-12 mwp-text-center">
                  <svg
                    width="104"
                    height="104"
                    viewBox="0 0 104 104"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    className="mwp-inline-block"
                  >
                    <circle cx="52" cy="52" r="52" fill="#EAEEEE" />
                    <path
                      fillRule="evenodd"
                      clipRule="evenodd"
                      d="M52.0002 25.2779C37.2419 25.2779 25.2779 37.2419 25.2779 52.0001C25.2779 66.7584 37.2419 78.7224 52.0002 78.7224C66.7584 78.7224 78.7224 66.7584 78.7224 52.0001C78.7224 37.2419 66.7584 25.2779 52.0002 25.2779ZM20.9446 52.0001C20.9446 34.8486 34.8487 20.9446 52.0002 20.9446C69.1517 20.9446 83.0557 34.8486 83.0557 52.0001C83.0557 69.1516 69.1517 83.0557 52.0002 83.0557C34.8487 83.0557 20.9446 69.1516 20.9446 52.0001Z"
                      fill="#BFCACC"
                    />
                    <path
                      fillRule="evenodd"
                      clipRule="evenodd"
                      d="M52.0002 38.2778C53.1968 38.2778 54.1668 39.2479 54.1668 40.4445V52.0001C54.1668 53.1967 53.1968 54.1667 52.0002 54.1667C50.8035 54.1667 49.8335 53.1967 49.8335 52.0001V40.4445C49.8335 39.2479 50.8035 38.2778 52.0002 38.2778Z"
                      fill="#BFCACC"
                    />
                    <path
                      fillRule="evenodd"
                      clipRule="evenodd"
                      d="M49.8335 63.5558C49.8335 62.3592 50.8035 61.3892 52.0002 61.3892H52.0291C53.2257 61.3892 54.1957 62.3592 54.1957 63.5558C54.1957 64.7524 53.2257 65.7225 52.0291 65.7225H52.0002C50.8035 65.7225 49.8335 64.7524 49.8335 63.5558Z"
                      fill="#BFCACC"
                    />
                  </svg>
                </div>
                <AlertDialog.Title className="mwp-mb-4 mwp-text-lg">
                  Are you sure you want to disconnect from Medusa?
                </AlertDialog.Title>
                <AlertDialog.Description className="mwp-mb-8 mwp-text-2xs">
                  By disconnecting your content will no longer be visible on the
                  site once disconnected.
                </AlertDialog.Description>
                <div className="mwp-flex mwp-justify-between">
                  <AlertDialog.Cancel asChild>
                    <UiButton variant="outline">Cancel</UiButton>
                  </AlertDialog.Cancel>
                  <AlertDialog.Action asChild>
                    <UiButton
                      onClick={async () => {
                        await store.disconnect();

                        setConnectionModalStep("success");
                      }}
                    >
                      Disconnect
                    </UiButton>
                  </AlertDialog.Action>
                </div>
              </UiAlertDialogContent>
            </AlertDialog.Content>
          </AlertDialog.Portal>
        </AlertDialog.Root>
        <AlertDialog.Root
          open={connectionModalStep === "success"}
          onOpenChange={(open) => {
            setConnectionModalStep(open ? "success" : false);
          }}
        >
          <AlertDialog.Portal container={mwpRoot}>
            <AlertDialog.Overlay asChild>
              <UiAlertDialogOverlay />
            </AlertDialog.Overlay>
            <AlertDialog.Content asChild>
              <UiAlertDialogContent>
                <AlertDialog.Title className="mwp-mb-4 mwp-text-lg">
                  Disconnected with Medusa
                </AlertDialog.Title>
                <AlertDialog.Description className="mwp-mb-8 mwp-text-2xs">
                  You are now disconnected from Medusa. Your content will no
                  longer be visible on the website. To reinstate it, simply
                  reconnect.
                </AlertDialog.Description>
                <AlertDialog.Cancel asChild>
                  <UiButton className="mwp-w-full">OK</UiButton>
                </AlertDialog.Cancel>
              </UiAlertDialogContent>
            </AlertDialog.Content>
          </AlertDialog.Portal>
        </AlertDialog.Root>
      </Popover.Content>
    </Popover.Root>
  );
};

const SettingsButton = () => (
  <NavLink
    to="/settings"
    className="mwp-h-15 mwp-w-15 mwp-flex mwp-items-center mwp-justify-center mwp-rounded-sm mwp-border mwp-border-grayscale-100 mwp-shadow-none mwp-transition-colors hover:mwp-border-blue-200 hover:mwp-bg-blue-200"
  >
    <div>
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width="24"
        height="24"
        fill="none"
      >
        <path
          fill="#0D0D0D"
          fillRule="evenodd"
          d="M12 10.2a1.8 1.8 0 1 0 0 3.6 1.8 1.8 0 0 0 0-3.6ZM9 12a3 3 0 1 1 6 0 3 3 0 0 1-6 0Z"
          clipRule="evenodd"
        />
        <path
          fill="#0D0D0D"
          fillRule="evenodd"
          d="M12 2.404a1.17 1.17 0 0 0-1.17 1.17v.162a2.248 2.248 0 0 1-1.362 2.057.702.702 0 0 1-.209.053 2.247 2.247 0 0 1-2.337-.506l-.006-.005-.056-.056a1.172 1.172 0 1 0-1.657 1.656l.062.062a2.247 2.247 0 0 1 .459 2.458 2.247 2.247 0 0 1-2.049 1.45h-.101a1.17 1.17 0 0 0 0 2.34h.162a2.248 2.248 0 0 1 2.055 1.358 2.247 2.247 0 0 1-.45 2.475l-.006.006-.056.056a1.172 1.172 0 1 0 1.656 1.657l.062-.062a2.247 2.247 0 0 1 2.458-.459 2.247 2.247 0 0 1 1.45 2.049v.1a1.17 1.17 0 0 0 2.34 0v-.161a2.247 2.247 0 0 1 1.358-2.055 2.247 2.247 0 0 1 2.475.45l.006.006.056.056a1.172 1.172 0 1 0 1.657-1.656l-.062-.062a2.247 2.247 0 0 1-.451-2.475 2.247 2.247 0 0 1 2.055-1.358H20.424a1.17 1.17 0 0 0 0-2.34h-.161a2.247 2.247 0 0 1-2.057-1.362.703.703 0 0 1-.053-.209 2.247 2.247 0 0 1 .506-2.337l.005-.006.056-.056a1.172 1.172 0 1 0-1.656-1.657l-.062.062a2.247 2.247 0 0 1-2.475.451 2.247 2.247 0 0 1-1.358-2.054V3.574A1.17 1.17 0 0 0 12 2.404Zm-1.82-.65a2.574 2.574 0 0 1 4.395 1.82v.083a.842.842 0 0 0 .51.77l.007.003a.843.843 0 0 0 .927-.166l.053-.053a2.576 2.576 0 1 1 3.642 3.642l-.053.053a.843.843 0 0 0-.166.927.7.7 0 0 1 .052.178.844.844 0 0 0 .721.415h.157a2.575 2.575 0 0 1 0 5.149h-.082a.842.842 0 0 0-.77.51l-.003.007a.843.843 0 0 0 .166.927l.053.053a2.576 2.576 0 1 1-3.642 3.642l-.053-.053a.843.843 0 0 0-.927-.166l-.007.003a.843.843 0 0 0-.51.77v.157a2.575 2.575 0 0 1-5.15 0v-.074a.843.843 0 0 0-.592-.781.843.843 0 0 0-.927.166l-.053.053a2.576 2.576 0 1 1-3.642-3.642l.053-.053a.843.843 0 0 0 .166-.927l-.003-.007a.843.843 0 0 0-.77-.51h-.158a2.575 2.575 0 0 1 0-5.15h.075a.843.843 0 0 0 .764-.55.703.703 0 0 1 .017-.042.843.843 0 0 0-.166-.927l-.053-.053a2.577 2.577 0 1 1 3.642-3.642l.053.053a.843.843 0 0 0 .927.166.702.702 0 0 1 .178-.052.843.843 0 0 0 .415-.721v-.158c0-.682.27-1.337.754-1.82Z"
          clipRule="evenodd"
        />
      </svg>
    </div>
  </NavLink>
);

export const Header: React.FC = () => {
  const store = useStore();

  return (
    <div className="mwp-border-b mwp-border-grayscale-100">
      <div className="mwp-items-center mwp-justify-between mwp-px-6 mwp-py-8 sm:mwp-flex sm:mwp-px-10 lg:mwp-px-16">
        <div
          className={twJoin(
            "mwp-flex mwp-items-center mwp-gap-3 md:mwp-gap-4",
            store.connection && "mwp-mb-4 sm:mwp-mb-0",
          )}
        >
          <div className="mwp-w-6 md:mwp-w-10">
            <img src={logo} alt="MedusaWP" />
          </div>
          <h1 className="mwp-cursor-default mwp-text-lg md:mwp-text-xl lg:mwp-text-xxl">
            MedusaWP
          </h1>
        </div>
        <div className="mwp-flex mwp-gap-2 md:mwp-gap-6">
          {store.connection && (
            <>
              <ConnectionButton connection={store.connection} />
              <SettingsButton />
            </>
          )}
        </div>
      </div>
      {store.connection && (
        <ul className="-mwp-mb-px mwp-flex mwp-gap-5 mwp-px-6 sm:mwp-px-10 md:mwp-gap-10 lg:mwp-px-16 [&_a.active]:mwp-border-b-grayscale-900 [&_a.active]:mwp-font-semibold [&_a.active]:mwp-text-grayscale-900">
          <li className="mwp-mb-0">
            <NavLink
              to="/"
              className="mwp-block mwp-border-b-2 mwp-border-b-transparent mwp-leading-12 mwp-shadow-none mwp-transition-colors hover:mwp-text-grayscale-700"
            >
              Sync data
            </NavLink>
          </li>
          <li className="mwp-mb-0">
            <NavLink
              to="/data-health"
              className="mwp-block mwp-border-b-2 mwp-border-b-transparent mwp-leading-12 mwp-shadow-none mwp-transition-colors hover:mwp-text-grayscale-700"
            >
              Data health
            </NavLink>
          </li>
        </ul>
      )}
    </div>
  );
};
