import * as React from "react";
import { getHighlighter } from "shikiji";
import * as Collapsible from "@radix-ui/react-collapsible";

import blulocoLightTheme from "assets/bluloco-light.json";
import { TSyncMessage } from "api/sync";
import { UiMessageBar } from "./ui/MessageBar";
import { UiButtonLink } from "./ui/Button";

const _shikiPromise = getHighlighter({
  themes: [
    {
      name: "bluloco-light",
      type: "light",
      settings: blulocoLightTheme.tokenColors,
      fg: blulocoLightTheme.colors["editor.foreground"],
      bg: blulocoLightTheme.colors["editor.background"],
      colors: blulocoLightTheme.colors,
    },
  ],
  langs: ["json"],
});

let _shiki: Awaited<ReturnType<typeof getHighlighter>> | undefined;
const subscribers: (() => void)[] = [];
const subscribeOnShiki = (onStoreChange: () => void) => {
  subscribers.push(onStoreChange);

  return () => {
    const index = subscribers.indexOf(onStoreChange);
    if (index > -1) {
      subscribers.splice(index, 1);
    }
  };
};
_shikiPromise.then((s) => {
  _shiki = s;

  subscribers.forEach((s) => s());
});

const getShiki = () => {
  return _shiki;
};

export const SyncMessage: React.FC<{ message: TSyncMessage }> = ({
  message,
}) => {
  const label = React.useMemo(
    () => new Date(message.started_at * 1000).toLocaleString(),
    [message.started_at],
  );
  const shiki = React.useSyncExternalStore(subscribeOnShiki, getShiki);

  if (message.data) {
    return (
      <Collapsible.Root>
        <Collapsible.Trigger asChild>
          <UiMessageBar
            label={label}
            message={message.message}
            variant={message.status}
            button={
              message.medusa_admin_link && (
                <UiButtonLink
                  href={message.medusa_admin_link}
                  variant="outline"
                  size="sm"
                >
                  Open in Medusa
                </UiButtonLink>
              )
            }
            isClickable
          />
        </Collapsible.Trigger>
        <Collapsible.Content>
          {!shiki ? (
            <div>Loading...</div>
          ) : (
            <div
              className="code-block"
              dangerouslySetInnerHTML={{
                __html: shiki.codeToHtml(
                  JSON.stringify(JSON.parse(message.data), null, 2),
                  {
                    lang: "json",
                    theme: "bluloco-light",
                  },
                ),
              }}
            />
          )}
        </Collapsible.Content>
      </Collapsible.Root>
    );
  }

  return (
    <UiMessageBar
      label={label}
      message={message.message}
      variant={message.status}
      button={
        message.medusa_admin_link && (
          <UiButtonLink
            href={message.medusa_admin_link}
            variant="outline"
            size="sm"
          >
            Open in Medusa
          </UiButtonLink>
        )
      }
    />
  );
};
