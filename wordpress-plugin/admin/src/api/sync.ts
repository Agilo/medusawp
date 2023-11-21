import { z } from "zod";
import { nonce, root } from "./config";

interface SyncRequestBody {
  import_thumbnails?: boolean;
}

const stringToNumber = (val: unknown) => {
  if (typeof val === "string" && val.trim().length) {
    return Number(val.trim());
  }

  if (typeof val === "number") {
    return val;
  }

  return null;
};

export const SyncMessageSchema = z.object({
  id: z.preprocess(stringToNumber, z.number()),
  model: z.string(),
  data: z.string(),
  medusa_admin_link: z.string().nullable(),
  message: z.string(),
  started_at: z.preprocess(stringToNumber, z.number()),
  ended_at: z.preprocess(stringToNumber, z.number().nullable()),
  sync_timestamp: z.preprocess(stringToNumber, z.number().nullable()),
  status: z.union([z.literal("error"), z.literal("success")]),
});
export type TSyncMessage = z.infer<typeof SyncMessageSchema>;

export const SyncMessagesResponseSchema = z.object({
  current_page: z.number(),
  last_page: z.number(),
  messages: z.array(SyncMessageSchema),
  total: z.number(),
});

const TotalsSchema = z.object({
  collections: z.number().optional(),
  product_variants: z.number().optional(),
  products: z.number().optional(),
  regions: z.number().optional(),
  thumbnails: z.number().optional(),
});

export const SyncedSchema = TotalsSchema;

export const MedusaWpSyncResponseSchema = z.object({
  started_at: z.number(),
  ended_at: z.number().nullable(),
  totals: TotalsSchema,
  synced: SyncedSchema,
  import_thumbnails: z.boolean(),
  type: z.union([
    z.literal("bulk_sync"),
    z.literal("import_thumbnails"),
    z.literal("bulk_sync_and_import_thumbnails"),
  ]),
});

export const MedusaWpSyncProgressResponseSchema = z.object({
  progress: MedusaWpSyncResponseSchema.extend({
    messages: z.array(SyncMessageSchema),
  }).nullable(),
});

export async function sync(body: SyncRequestBody = {}) {
  const response = await fetch(`${root}wp/v2/admin/medusa/sync`, {
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      "X-WP-Nonce": nonce,
    },
    credentials: "include",
    method: "POST",
    body: JSON.stringify(body),
  });

  if (response.status === 200) {
    const data = await response.json();

    return MedusaWpSyncResponseSchema.parse(data);
  }

  throw new Error("Unknown error");
}

export async function getSyncMessages(options?: {
  page?: number;
  per_page?: number;
  status?: "error" | "success";
}) {
  let url = `${root}wp/v2/admin/medusa/sync-messages`;

  if (options) {
    const params = new URLSearchParams();

    if (options.page) {
      params.append("page", options.page.toString());
    }

    if (options.per_page) {
      params.append("per_page", options.per_page.toString());
    }

    if (options.status) {
      params.append("status", options.status);
    }

    url += `?${params.toString()}`;
  }

  const response = await fetch(url, {
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      "X-WP-Nonce": nonce,
    },
    credentials: "include",
    method: "GET",
  });

  if (response.status === 200) {
    const data = await response.json();

    return SyncMessagesResponseSchema.parse(data);
  }

  throw new Error("Unknown error");
}

export async function getSyncProgress() {
  const response = await fetch(`${root}wp/v2/admin/medusa/sync-progress`, {
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      "X-WP-Nonce": nonce,
    },
    credentials: "include",
    method: "GET",
  });

  if (response.status === 200) {
    const data = await response.json();

    return MedusaWpSyncProgressResponseSchema.parse(data);
  }

  throw new Error("Unknown error");
}

export async function removeSyncedData() {
  const response = await fetch(`${root}wp/v2/admin/medusa/remove-synced`, {
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      "X-WP-Nonce": nonce,
    },
    credentials: "include",
    method: "POST",
  });

  if (response.status === 200) {
    return;
  }

  throw new Error("Unknown error");
}

export async function startImportThumbnails() {
  const response = await fetch(`${root}wp/v2/admin/medusa/import-thumbnails`, {
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      "X-WP-Nonce": nonce,
    },
    credentials: "include",
    method: "POST",
  });

  if (response.status === 200) {
    return MedusaWpSyncResponseSchema.parse(await response.json());
  }

  throw new Error("Unknown error");
}
