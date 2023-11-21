import { z } from "zod";
import { nonce, root } from "./config";

export const MedusaWPSettingsSchema = z.object({
  default_country: z.string(),
  always_import_thumbnails: z.boolean(),
});

export type MedusaWPSettingsType = z.infer<typeof MedusaWPSettingsSchema>;

export async function getSettings() {
  const response = await fetch(`${root}wp/v2/admin/medusa/settings`, {
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      "X-WP-Nonce": nonce,
    },
    credentials: "include",
    method: "GET",
  });

  if (response.status === 200) {
    return MedusaWPSettingsSchema.parse(await response.json());
  }

  throw new Error("Unknown error");
}

export async function updateSettings(settings: MedusaWPSettingsType) {
  const response = await fetch(`${root}wp/v2/admin/medusa/settings`, {
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      "X-WP-Nonce": nonce,
    },
    credentials: "include",
    method: "POST",
    body: JSON.stringify(settings),
  });

  if (response.status === 200) {
    return MedusaWPSettingsSchema.parse(await response.json());
  }

  throw new Error("Unknown error");
}

const CountrySchema = z.object({
  id: z.string(),
  iso_2: z.string(),
  iso_3: z.string(),
  num_code: z.string(),
  name: z.string(),
  display_name: z.string(),
  region_id: z.string(),
  synced_at: z.string(),
});

const RegionSchema = z
  .object({
    id: z.string(),
    name: z.string(),
    currency_code: z.string(),
    tax_rate: z.string(),
    tax_code: z.string().nullable(),
    created_at: z.string(),
    updated_at: z.string(),
    deleted_at: z.string().nullable(),
    metadata: z.union([z.null(), z.record(z.unknown())]),
    countries: z.array(CountrySchema),
    gift_cards_taxable: z.string(),
    automatic_taxes: z.string(),
    includes_tax: z.string(),
    tax_provider_id: z.string().nullable(),
    synced_at: z.string(),
    sync_status: z.string().nullable(),
  })
  .strict();

const RegionsResponseSchema = z.array(RegionSchema);

export type RegionsResponseType = z.infer<typeof RegionsResponseSchema>;

export async function getRegions() {
  const response = await fetch(`${root}wp/v2/medusa/region`, {
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      "X-WP-Nonce": nonce,
    },
    credentials: "include",
    method: "GET",
  });

  if (response.status === 200) {
    return RegionsResponseSchema.parse(await response.json());
  }

  throw new Error("Unknown error");
}
