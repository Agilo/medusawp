import * as React from "react";
import { HashRouter, Route, Routes, useSearchParams } from "react-router-dom";
import {
  QueryClient,
  QueryClientProvider,
  useMutation,
  useQuery,
  useQueryClient,
} from "@tanstack/react-query";
import { FormProvider, useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { formatDistance } from "date-fns";
import * as AlertDialog from "@radix-ui/react-alert-dialog";

import { UiCard } from "components/ui/Card";
import { UiButton, UiButtonNavLink } from "components/ui/Button";
import { UiInput } from "components/ui/Input";
import { UiLabel } from "components/ui/Label";
import { UiCheckbox } from "components/ui/Checkbox";
import { UiProgressBar } from "components/ui/ProgressBar";
import { UiSelect } from "components/ui/Select";
import { Header } from "components/Header";
import { StoreProvider } from "store/provider";
import { useDisconnectedStore, useStore } from "store/hooks";
import {
  MedusaWPSettingsSchema,
  MedusaWPSettingsType,
  RegionsResponseType,
  getRegions,
  getSettings,
  updateSettings,
} from "api/settings";
import {
  getSyncMessages,
  getSyncProgress,
  removeSyncedData,
  startImportThumbnails,
  sync,
} from "api/sync";
import { Pagination } from "components/Pagination";
import {
  UiAlertDialogContent,
  UiAlertDialogOverlay,
} from "components/ui/AlertDialog";
import { twJoin } from "tailwind-merge";
import { SyncMessage } from "components/SyncMessage";

const connectionFormSchema = z.object({
  url: z.string().url(),
  email: z.string().email(),
  password: z.string(),
});

const ConnectionScreen: React.FC = () => {
  const store = useDisconnectedStore();
  const form = useForm({
    resolver: zodResolver(connectionFormSchema),
    defaultValues: {
      url: "",
      email: "",
      password: "",
    },
  });

  return (
    <div className="mwp-mx-auto mwp-p-2 sm:mwp-p-6 lg:mwp-max-w-126 lg:mwp-p-0">
      <div className="mwp-mb-10 md:mwp-mb-16">
        <h2 className="mwp-mb-4 mwp-text-md md:mwp-text-lg">
          Connect with Medusa
        </h2>
        <p>Connect your Medusa e-commerce with WordPress.</p>
      </div>
      <FormProvider {...form}>
        <form
          onSubmit={form.handleSubmit(async (data) => {
            await store.connect(data);
          })}
        >
          <div className="mwp-mb-6 md:mwp-mb-8">
            <UiLabel htmlFor="url">Medusa URL</UiLabel>
            <UiInput
              {...form.register("url")}
              id="url"
              type="url"
              placeholder="URL"
            />
          </div>
          <div className="mwp-mb-6 md:mwp-mb-8">
            <UiLabel htmlFor="email">Admin email</UiLabel>
            <UiInput
              {...form.register("email")}
              id="email"
              type="email"
              placeholder="Email"
            />
          </div>
          <div className="mwp-mb-8 md:mwp-mb-10">
            <UiLabel htmlFor="password">Admin password</UiLabel>
            <UiInput
              {...form.register("password")}
              id="password"
              type="password"
              placeholder="Password"
            />
          </div>
          <div>
            <UiButton type="submit" isDisabled={form.formState.isSubmitting}>
              Connect
            </UiButton>
          </div>
        </form>
      </FormProvider>
    </div>
  );
};

const SettingsForm: React.FC<{
  defaultValues: MedusaWPSettingsType;
  regions: RegionsResponseType;
}> = ({ defaultValues, regions }) => {
  const form = useForm({
    resolver: zodResolver(MedusaWPSettingsSchema),
    defaultValues,
  });
  const updateSettingsMutation = useMutation({
    mutationKey: ["medusawp", "wp", "settings"],
    mutationFn: updateSettings,
  });

  return (
    <FormProvider {...form}>
      <form
        onSubmit={form.handleSubmit(async (data) => {
          await updateSettingsMutation.mutateAsync(data);
        })}
      >
        <div className="mwp-mb-8 md:mwp-mb-10">
          <UiLabel htmlFor="default_country">Default country</UiLabel>
          <UiSelect {...form.register("default_country")} id="default_country">
            <option disabled>Select country</option>
            {regions.map((region) => (
              <optgroup key={region.id} label={region.name}>
                {region.countries.map((country) => (
                  <option key={country.id} value={country.iso_2}>
                    {country.display_name}
                  </option>
                ))}
              </optgroup>
            ))}
          </UiSelect>
        </div>
        <div className="mwp-mb-8 md:mwp-mb-10">
          <UiCheckbox
            {...form.register("always_import_thumbnails")}
            label="Import product thumbnails to Media Library"
          />
        </div>
        <div>
          <UiButton type="submit" isDisabled={form.formState.isSubmitting}>
            Save
          </UiButton>
        </div>
      </form>
    </FormProvider>
  );
};

const SettingsPanel: React.FC = () => {
  const settingsQuery = useQuery({
    queryKey: ["medusawp", "wp", "settings"],
    queryFn: getSettings,
  });
  const regionsQuery = useQuery({
    queryKey: ["medusawp", "wp", "regions"],
    queryFn: getRegions,
  });

  return (
    <div className="mwp-mx-auto mwp-p-2 sm:mwp-p-6 lg:mwp-max-w-126 lg:mwp-p-0">
      <div className="mwp-mb-10 md:mwp-mb-16">
        <h2 className="mwp-mb-4 mwp-text-md md:mwp-text-lg">Settings</h2>
      </div>
      {(settingsQuery.isLoading || regionsQuery.isLoading) && <p>Loading...</p>}
      {settingsQuery.isSuccess && regionsQuery.isSuccess && (
        <SettingsForm
          defaultValues={settingsQuery.data}
          regions={regionsQuery.data}
        />
      )}
    </div>
  );
};

interface SyncData {
  totalItems: number;
  previousSyncedItems: number;
  syncedItems: number;
  startedAt: number;
}

function calculateRemainingTime(
  syncData: SyncData,
  currentTime: number,
): number {
  if (!syncData.syncedItems) {
    return syncData.totalItems ? syncData.totalItems * 1000 : 0;
  }

  if (syncData.syncedItems >= syncData.totalItems) {
    return 0;
  }

  const elapsedTime = currentTime - syncData.startedAt;
  const rate = syncData.syncedItems / elapsedTime;

  const remainingItems = syncData.totalItems - syncData.syncedItems;
  const estimatedRemainingTime = remainingItems / rate;
  return estimatedRemainingTime;
}

const SyncInProgress: React.FC<{
  progress: Exclude<
    Awaited<ReturnType<typeof getSyncProgress>>["progress"],
    null
  >;
}> = ({ progress }) => {
  const startedAt = React.useMemo(
    () => new Date(progress.started_at * 1000),
    [progress.started_at],
  );
  const previousSyncedItems = React.useRef(0);
  const syncedItems = React.useMemo(
    () => Object.values(progress.synced).reduce((acc, curr) => acc + curr, 0),
    [progress.synced],
  );
  const totalItems = Object.values(progress.totals).reduce(
    (acc, curr) => acc + curr,
    0,
  );
  const percent =
    totalItems === 0 || syncedItems === 0
      ? 0
      : Math.round((syncedItems / totalItems) * 100);

  const timeLeft = calculateRemainingTime(
    {
      startedAt: startedAt.getTime(),
      totalItems,
      syncedItems,
      previousSyncedItems: previousSyncedItems.current,
    },
    Date.now(),
  );

  React.useEffect(() => {
    previousSyncedItems.current = syncedItems;
  }, [syncedItems]);

  return (
    <UiCard>
      <div className="mwp-items-baseline mwp-justify-between mwp-gap-8 md:mwp-flex">
        <h2 className="mwp-text-md md:mwp-text-lg">
          {progress.type === "import_thumbnails"
            ? "Importing Medusa Thumbnails to Media Library"
            : "Sync Medusa Data is in progress"}
        </h2>
        <p className="mwp-mt-2 mwp-shrink-0 md:mwp-mt-0">
          Started:{" "}
          <strong className="mwp-font-normal mwp-text-grayscale-900">
            {startedAt.toLocaleString()}
          </strong>
        </p>
      </div>
      <UiProgressBar value={percent} className="mwp-mt-8" />
      <div className="mwp-mt-8 mwp-flex mwp-gap-4">
        <p className="mwp-text-grayscale-900">
          {syncedItems} of {totalItems} items
        </p>
        <p>
          {!syncedItems
            ? "calculating..."
            : `${formatDistance(timeLeft, 0)} left`}
        </p>
      </div>
      {progress.messages.length > 0 && (
        <>
          <h3 className="mwp-mt-12 mwp-text-sm md:mwp-text-md">
            Errors ({progress.messages.length})
          </h3>
          <div className="mwp-mt-6 mwp-flex mwp-flex-col mwp-gap-4">
            {progress.messages.map((message) => (
              <SyncMessage key={message.id} message={message} />
            ))}
          </div>
          <p className="mwp-mt-4 mwp-text-2xs">
            It seems like there were some error in your data. Please check your
            data in Medusa admin. Items will be automatically synced when
            corrected in Medusa.
          </p>
        </>
      )}
      {progress.type !== "import_thumbnails" && (
        <div className="mwp-mt-12">
          <UiCheckbox
            label="Media Library will automatically import after data sync."
            disabled
            checked={progress.import_thumbnails}
          />
        </div>
      )}
    </UiCard>
  );
};

const ImportThumbnailsCheckbox: React.FC<{
  defaultChecked: boolean;
}> = ({ defaultChecked }) => {
  const [isChecked, setIsChecked] = React.useState(defaultChecked);

  return (
    <div>
      <UiCheckbox
        label="Import product thumbnails to Media Library"
        name="import_thumbnails"
        defaultChecked={defaultChecked}
        onChange={(event) => {
          setIsChecked(event.target.checked);
        }}
      />
      {isChecked && (
        <div className="mwp-mt-2 mwp-flex mwp-items-center mwp-gap-2 mwp-text-orange-500">
          <div className="mwp-h-4 mwp-w-4">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              fill="none"
            >
              <path
                fill="currentcolor"
                fillRule="evenodd"
                d="M7.07 1.74a1.833 1.833 0 0 1 2.518.68l5.332 9.33v.001a1.833 1.833 0 0 1-1.586 2.749H2.669a1.833 1.833 0 0 1-1.603-2.749l.001-.001 5.331-9.33c.16-.282.392-.517.672-.68Zm.923.75a.833.833 0 0 0-.725.423v.002L1.932 12.25a.833.833 0 0 0 .73 1.25h10.67a.833.833 0 0 0 .72-1.25v-.002L8.719 2.915v-.002a.833.833 0 0 0-.726-.422Z"
                clipRule="evenodd"
              />
              <path
                fill="currentcolor"
                fillRule="evenodd"
                d="M8 5.5a.5.5 0 0 1 .5.5v2.667a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5ZM7.5 11.334a.5.5 0 0 1 .5-.5h.007a.5.5 0 0 1 0 1H8a.5.5 0 0 1-.5-.5Z"
                clipRule="evenodd"
              />
            </svg>
          </div>
          <div>
            <p className="mwp-text-2xs">
              This action may slow down initial data sync. For a smoother
              experience, consider importing thumbnails separately after data
              sync.
            </p>
          </div>
        </div>
      )}
    </div>
  );
};

const SyncPanel: React.FC = () => {
  const mwpRoot = React.useMemo(() => document.getElementById("mwp-root"), []);
  const queryClient = useQueryClient();
  const settingsQuery = useQuery({
    queryKey: ["medusawp", "wp", "settings"],
    queryFn: getSettings,
  });
  const [syncStatus, setSyncStatus] = React.useState<
    | { status: "idle" | "initial" }
    | { status: "syncing" | "success"; type: "bulk" | "thumbnails" }
  >({ status: "initial" });
  const syncProgressQuery = useQuery({
    queryKey: ["medusawp", "wp", "sync-progress"],
    queryFn: getSyncProgress,
    refetchInterval: (query) => {
      const data = query.state.data;

      if (data?.progress && data.progress.ended_at === null) {
        return 500;
      }

      return false;
    },
  });
  const startBulkSyncMutation = useMutation({
    mutationKey: ["medusawp", "wp", "start-bulk-sync"],
    mutationFn: sync,
    onMutate(variables) {
      setSyncStatus({ status: "syncing", type: "bulk" });
      queryClient.setQueryData<Awaited<ReturnType<typeof getSyncProgress>>>(
        ["medusawp", "wp", "sync-progress"],
        {
          progress: {
            import_thumbnails: Boolean(variables?.import_thumbnails),
            ended_at: null,
            started_at: Date.now() / 1000,
            messages: [],
            synced: {},
            totals: {},
            type: variables?.import_thumbnails
              ? "bulk_sync_and_import_thumbnails"
              : "bulk_sync",
          },
        },
      );
    },
    onError: () => {
      // set to initial to allow picking up state from the sync progress response
      setSyncStatus({ status: "initial" });
      queryClient.setQueryData<Awaited<ReturnType<typeof getSyncProgress>>>(
        ["medusawp", "wp", "sync-progress"],
        {
          progress: null,
        },
      );
      syncProgressQuery.refetch();
    },
    onSuccess: (data) => {
      setSyncStatus({ status: "syncing", type: "bulk" });
      queryClient.setQueryData<Awaited<ReturnType<typeof getSyncProgress>>>(
        ["medusawp", "wp", "sync-progress"],
        {
          progress: {
            import_thumbnails: Boolean(data.import_thumbnails),
            ended_at: null,
            started_at: data.started_at,
            messages: [],
            synced: data.synced,
            totals: data.totals,
            type: data.type,
          },
        },
      );
      syncProgressQuery.refetch();
    },
  });
  const startThumbnailImportMutation = useMutation({
    mutationKey: ["medusawp", "wp", "start-thumbnail-import"],
    mutationFn: startImportThumbnails,
    onSuccess: (data) => {
      setSyncStatus({ status: "syncing", type: "thumbnails" });
      queryClient.setQueryData<Awaited<ReturnType<typeof getSyncProgress>>>(
        ["medusawp", "wp", "sync-progress"],
        {
          progress: {
            import_thumbnails: Boolean(data.import_thumbnails),
            ended_at: null,
            started_at: data.started_at,
            messages: [],
            synced: data.synced,
            totals: data.totals,
            type: data.type,
          },
        },
      );
      syncProgressQuery.refetch();
    },
  });
  const removeSyncedDataMutation = useMutation({
    mutationKey: ["medusawp", "wp", "remove-synced-data"],
    mutationFn: removeSyncedData,
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: ["medusawp", "wp", "sync-messages"],
      });
      queryClient.invalidateQueries({
        queryKey: ["medusawp", "wp", "sync-progress"],
      });
    },
  });

  React.useEffect(() => {
    if (syncProgressQuery.data?.progress?.ended_at !== null) {
      setSyncStatus((val) =>
        val.status === "syncing" ? { status: "success", type: val.type } : val,
      );
    }
  }, [syncProgressQuery.data?.progress?.ended_at]);

  React.useEffect(() => {
    if (syncStatus.status !== "initial" || !syncProgressQuery.data) {
      return;
    }

    if (
      syncProgressQuery.data.progress &&
      !syncProgressQuery.data.progress.ended_at
    ) {
      setSyncStatus({
        status: "syncing",
        type:
          syncProgressQuery.data.progress.type === "import_thumbnails"
            ? "thumbnails"
            : "bulk",
      });
    } else {
      setSyncStatus({ status: "idle" });
    }
  }, [syncProgressQuery.data, syncStatus.status]);

  const disableRemoveSyncedData =
    syncStatus.status === "syncing" || !syncProgressQuery.data;

  if (settingsQuery.isLoading) {
    return <p>Loading...</p>;
  }

  return (
    <div className="mwp-flex mwp-flex-col mwp-gap-4 lg:mwp-gap-10">
      {/* Start sync */}
      {(syncStatus.status === "idle" || syncStatus.status === "initial") && (
        <UiCard className="mwp-flex mwp-min-h-64 mwp-flex-col mwp-justify-between mwp-gap-14">
          <div>
            <h2 className="mwp-text-md md:mwp-text-lg">Sync Medusa Data</h2>
            <div className="mwp-mt-4">
              <p>Synchronize your Medusa e-commerce data with WordPress.</p>
            </div>
          </div>
          <form
            className="mwp-items-end mwp-justify-between mwp-gap-10 md:mwp-flex"
            onSubmit={(event) => {
              event.preventDefault();
              const formData = new FormData(event.target as HTMLFormElement);

              startBulkSyncMutation.mutate({
                import_thumbnails: Boolean(formData.get("import_thumbnails")),
              });
            }}
          >
            <ImportThumbnailsCheckbox
              defaultChecked={
                settingsQuery.data?.always_import_thumbnails || false
              }
            />
            <div className="mwp-mt-6 md:mwp-mt-0">
              <UiButton
                type="submit"
                disabled={startBulkSyncMutation.isPending}
              >
                Sync
              </UiButton>
            </div>
          </form>
        </UiCard>
      )}

      {/* Sync in progress */}
      {(syncStatus.status === "syncing" || syncStatus.status === "success") &&
        typeof syncProgressQuery.data !== "undefined" &&
        syncProgressQuery.data.progress !== null && (
          <SyncInProgress progress={syncProgressQuery.data.progress} />
        )}

      {/* Sync finished modal */}
      <AlertDialog.Root
        open={syncStatus.status === "success" && syncStatus.type === "bulk"}
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
                    d="M20.9448 52.0009C20.9448 34.8489 34.8484 20.9453 52.0004 20.9453C69.1523 20.9453 83.0559 34.8489 83.0559 52.0009C83.0559 69.1528 69.1523 83.0564 52.0004 83.0564C34.8484 83.0564 20.9448 69.1528 20.9448 52.0009ZM52.0004 25.2786C37.2417 25.2786 25.2782 37.2422 25.2782 52.0009C25.2782 66.7596 37.2417 78.7231 52.0004 78.7231C66.7591 78.7231 78.7226 66.7596 78.7226 52.0009C78.7226 37.2422 66.7591 25.2786 52.0004 25.2786Z"
                    fill="#BFCACC"
                  />
                  <path
                    fillRule="evenodd"
                    clipRule="evenodd"
                    d="M62.1991 44.6912C63.0452 45.5374 63.0452 46.9092 62.1991 47.7554L50.6435 59.3109C49.7974 60.1571 48.4255 60.1571 47.5794 59.3109L41.8016 53.5331C40.9555 52.687 40.9555 51.3152 41.8016 50.469C42.6477 49.6229 44.0196 49.6229 44.8657 50.469L49.1114 54.7147L59.1349 44.6912C59.9811 43.8451 61.3529 43.8451 62.1991 44.6912Z"
                    fill="#BFCACC"
                  />
                </svg>
              </div>
              <AlertDialog.Title className="mwp-mb-4 mwp-text-lg">
                Sync Medusa Data Finished
              </AlertDialog.Title>
              <AlertDialog.Description className="mwp-mb-8 mwp-text-2xs">
                Data health report will open automatically, allowing you to
                review and inspect your synced information.
              </AlertDialog.Description>
              <AlertDialog.Cancel asChild>
                <UiButtonNavLink to="/settings" className="mwp-w-full">
                  OK
                </UiButtonNavLink>
              </AlertDialog.Cancel>
            </UiAlertDialogContent>
          </AlertDialog.Content>
        </AlertDialog.Portal>
      </AlertDialog.Root>

      {/* Start thumbnail import */}
      {(syncStatus.status === "idle" || syncStatus.status === "initial") && (
        <UiCard className="mwp-items-end mwp-justify-between mwp-gap-10 md:mwp-flex">
          <div className="mwp-max-w-148">
            <h2 className="mwp-text-md md:mwp-text-lg">
              Import product thumbnails to Media Library
            </h2>
            <p className="mwp-mt-4">
              This will import all your Medusa product thumbnails to WordPress
              Media Library in the background process.
            </p>
          </div>
          <div className="mwp-mt-6 md:mwp-mt-0">
            <UiButton
              variant="outline"
              onClick={() => {
                startThumbnailImportMutation.mutate();
              }}
            >
              Import
            </UiButton>
          </div>
        </UiCard>
      )}

      {/* Thumbnail import finished modal */}
      <AlertDialog.Root
        open={
          syncStatus.status === "success" && syncStatus.type === "thumbnails"
        }
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
                    d="M20.9448 52.0009C20.9448 34.8489 34.8484 20.9453 52.0004 20.9453C69.1523 20.9453 83.0559 34.8489 83.0559 52.0009C83.0559 69.1528 69.1523 83.0564 52.0004 83.0564C34.8484 83.0564 20.9448 69.1528 20.9448 52.0009ZM52.0004 25.2786C37.2417 25.2786 25.2782 37.2422 25.2782 52.0009C25.2782 66.7596 37.2417 78.7231 52.0004 78.7231C66.7591 78.7231 78.7226 66.7596 78.7226 52.0009C78.7226 37.2422 66.7591 25.2786 52.0004 25.2786Z"
                    fill="#BFCACC"
                  />
                  <path
                    fillRule="evenodd"
                    clipRule="evenodd"
                    d="M62.1991 44.6912C63.0452 45.5374 63.0452 46.9092 62.1991 47.7554L50.6435 59.3109C49.7974 60.1571 48.4255 60.1571 47.5794 59.3109L41.8016 53.5331C40.9555 52.687 40.9555 51.3152 41.8016 50.469C42.6477 49.6229 44.0196 49.6229 44.8657 50.469L49.1114 54.7147L59.1349 44.6912C59.9811 43.8451 61.3529 43.8451 62.1991 44.6912Z"
                    fill="#BFCACC"
                  />
                </svg>
              </div>
              <AlertDialog.Title className="mwp-mb-4 mwp-text-lg">
                Thumbnail import finished
              </AlertDialog.Title>
              <AlertDialog.Description className="mwp-mb-8 mwp-text-2xs">
                Data health report will open automatically, allowing you to
                review and inspect your imported files.
              </AlertDialog.Description>
              <AlertDialog.Cancel asChild>
                <UiButtonNavLink to="/data-health" className="mwp-w-full">
                  OK
                </UiButtonNavLink>
              </AlertDialog.Cancel>
            </UiAlertDialogContent>
          </AlertDialog.Content>
        </AlertDialog.Portal>
      </AlertDialog.Root>

      {/* Remove synced data */}

      <UiCard
        isDisabled={disableRemoveSyncedData}
        className="mwp-items-end mwp-justify-between mwp-gap-10 mwp-border-0 mwp-bg-grayscale-50 md:mwp-flex"
      >
        <div className="mwp-max-w-148">
          <h2
            className={twJoin(
              "mwp-text-md md:mwp-text-lg",
              disableRemoveSyncedData && "mwp-text-grayscale-200",
            )}
          >
            Remove all synced data
          </h2>
          <p className="mwp-mt-4">
            This will remove Medusa data and all the custom content for product
            and collection. Please backup your data before proceeding.
          </p>
        </div>
        <div className="mwp-mt-6 md:mwp-mt-0">
          <UiButton
            variant="outline"
            isDisabled={disableRemoveSyncedData}
            onClick={() => {
              removeSyncedDataMutation.mutate();
            }}
          >
            Remove
          </UiButton>
        </div>
      </UiCard>
    </div>
  );
};

const DataHealthPanel: React.FC = () => {
  const [searchParams] = useSearchParams();
  const errorsPage = Number(searchParams.get("epage")) || 1;
  const successPage = Number(searchParams.get("spage")) || 1;

  const syncErrorMessagesQuery = useQuery({
    queryKey: ["medusawp", "wp", "sync-messages", "error", errorsPage],
    queryFn: () =>
      getSyncMessages({
        status: "error",
        page: errorsPage,
      }),
    refetchInterval: errorsPage === 1 ? 5000 : false,
  });
  const syncSuccessMessagesQuery = useQuery({
    queryKey: ["medusawp", "wp", "sync-messages", "success", successPage],
    queryFn: () =>
      getSyncMessages({
        status: "success",
        page: successPage,
      }),
    refetchInterval: successPage === 1 ? 5000 : false,
  });

  const dataUpdatedAt = React.useMemo(() => {
    const updatedAt = [
      syncErrorMessagesQuery.dataUpdatedAt,
      syncSuccessMessagesQuery.dataUpdatedAt,
    ].sort((a, b) => b - a)[0];

    return updatedAt ? new Date(updatedAt) : undefined;
  }, [
    syncErrorMessagesQuery.dataUpdatedAt,
    syncSuccessMessagesQuery.dataUpdatedAt,
  ]);

  return (
    <div className="mwp-p-2 sm:mwp-p-6 lg:mwp-p-0">
      <div className="mwp-mb-10 mwp-items-baseline mwp-justify-between mwp-gap-8 md:mwp-mb-16 md:mwp-flex">
        <div>
          <h2 className="mwp-mb-4 mwp-text-md md:mwp-text-lg">Data health</h2>
          <p>Here you can check all your synced items.</p>
        </div>
        <p className="mwp-mt-2 mwp-shrink-0 md:mwp-mt-0">
          Updated At:{" "}
          <strong className="mwp-font-normal mwp-text-grayscale-900">
            {dataUpdatedAt?.toLocaleString() ?? "Loading..."}
          </strong>
        </p>
      </div>
      <div className="mwp-mb-10 md:mwp-mb-16">
        <div className="mwp-mb-8 mwp-max-w-158">
          <h3 className="mwp-mb-4 mwp-text-md">Errors</h3>
          {syncErrorMessagesQuery.isSuccess &&
            syncErrorMessagesQuery.data.messages.length > 0 && (
              <p>
                There were some error in your data. Please check your data in
                Medusa. Items will be automatically synced when corrected in
                Medusa.
              </p>
            )}
        </div>
        <div className="mwp-flex mwp-flex-col mwp-gap-4">
          {syncErrorMessagesQuery.isLoading && <p>Loading messages...</p>}
          {syncErrorMessagesQuery.isSuccess && (
            <>
              {syncErrorMessagesQuery.data.messages.length > 0 ? (
                syncErrorMessagesQuery.data.messages.map((message) => (
                  <SyncMessage key={message.id} message={message} />
                ))
              ) : (
                <p>No messages.</p>
              )}
            </>
          )}
        </div>
      </div>
      {syncErrorMessagesQuery.isSuccess && (
        <div className="mwp-mb-10 mwp-text-center md:mwp-mb-16">
          <Pagination
            lastPage={syncErrorMessagesQuery.data?.last_page}
            queryKey="epage"
          />
        </div>
      )}
      <div className="mwp-mb-10 md:mwp-mb-16">
        <div className="mwp-mb-8 mwp-max-w-158">
          <h3 className="mwp-mb-4 mwp-text-md">Successfully synced</h3>
          {syncSuccessMessagesQuery.isSuccess &&
            syncSuccessMessagesQuery.data.messages.length > 0 && (
              <p>
                Data is successfully synced. For editing go to Medusa. Items
                will be automatically synced.
              </p>
            )}
        </div>
        <div className="mwp-flex mwp-flex-col mwp-gap-4">
          {syncSuccessMessagesQuery.isLoading && <p>Loading messages...</p>}
          {syncSuccessMessagesQuery.isSuccess && (
            <>
              {syncSuccessMessagesQuery.data.messages.length > 0 ? (
                syncSuccessMessagesQuery.data.messages.map((message) => (
                  <SyncMessage key={message.id} message={message} />
                ))
              ) : (
                <p>No messages.</p>
              )}
            </>
          )}
        </div>
      </div>
      {syncSuccessMessagesQuery.isSuccess && (
        <div className="mwp-text-center">
          <Pagination
            lastPage={syncSuccessMessagesQuery.data?.last_page}
            queryKey="spage"
          />
        </div>
      )}
    </div>
  );
};

const DashboardScreen: React.FC = () => {
  return (
    <Routes>
      <Route path="/" element={<SyncPanel />} />
      <Route path="/data-health" element={<DataHealthPanel />} />
      <Route path="/settings" element={<SettingsPanel />} />
    </Routes>
  );
};

const Main: React.FC = () => {
  const store = useStore();

  if (!store.connection) {
    return <ConnectionScreen />;
  }

  return <DashboardScreen />;
};

const queryClient = new QueryClient();

const App: React.FC = () => {
  return (
    <HashRouter>
      <QueryClientProvider client={queryClient}>
        <StoreProvider>
          <div id="mwp-body">
            <Header />
            <div className="mwp-p-4 lg:mwp-p-16">
              <Main />
            </div>
          </div>
        </StoreProvider>
      </QueryClientProvider>
    </HashRouter>
  );
};

export default App;
