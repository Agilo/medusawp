import { faker } from "@faker-js/faker";
import fs from "fs-extra";

const baseSeed = {
  store: {
    currencies: ["eur", "usd"],
  },
  users: [
    {
      email: "admin@medusa-test.com",
      password: "supersecret",
    },
  ],
  regions: [
    {
      id: "test-region-eu",
      name: "EU",
      currency_code: "eur",
      tax_rate: 0,
      payment_providers: ["manual"],
      fulfillment_providers: ["manual"],
      countries: ["gb", "de", "dk", "se", "fr", "es", "it"],
    },
    {
      id: "test-region-na",
      name: "NA",
      currency_code: "usd",
      tax_rate: 0,
      payment_providers: ["manual"],
      fulfillment_providers: ["manual"],
      countries: ["us", "ca"],
    },
  ],
  shipping_options: [
    {
      name: "PostFake Standard",
      region_id: "test-region-eu",
      provider_id: "manual",
      data: {
        id: "manual-fulfillment",
      },
      price_type: "flat_rate",
      amount: 1000,
    },
    {
      name: "PostFake Express",
      region_id: "test-region-eu",
      provider_id: "manual",
      data: {
        id: "manual-fulfillment",
      },
      price_type: "flat_rate",
      amount: 1500,
    },
    {
      name: "PostFake Return",
      region_id: "test-region-eu",
      provider_id: "manual",
      data: {
        id: "manual-fulfillment",
      },
      price_type: "flat_rate",
      is_return: true,
      amount: 1000,
    },
    {
      name: "I want to return it myself",
      region_id: "test-region-eu",
      provider_id: "manual",
      data: {
        id: "manual-fulfillment",
      },
      price_type: "flat_rate",
      is_return: true,
      amount: 0,
    },
    {
      name: "FakeEx Standard",
      region_id: "test-region-na",
      provider_id: "manual",
      data: {
        id: "manual-fulfillment",
      },
      price_type: "flat_rate",
      amount: 800,
    },
    {
      name: "FakeEx Express",
      region_id: "test-region-na",
      provider_id: "manual",
      data: {
        id: "manual-fulfillment",
      },
      price_type: "flat_rate",
      amount: 1200,
    },
    {
      name: "FakeEx Return",
      region_id: "test-region-na",
      provider_id: "manual",
      data: {
        id: "manual-fulfillment",
      },
      price_type: "flat_rate",
      is_return: true,
      amount: 800,
    },
    {
      name: "I want to return it myself",
      region_id: "test-region-na",
      provider_id: "manual",
      data: {
        id: "manual-fulfillment",
      },
      price_type: "flat_rate",
      is_return: true,
      amount: 0,
    },
  ],
  products: [],
};

const sizes = ["S", "M", "L", "XL"];
const colors = ["Black", "White"];

const generateVariant = (size: string, color: string) => ({
  title: `${size} / ${color}`,
  prices: [
    {
      currency_code: "eur",
      amount: faker.number.int({ min: 1500, max: 25000 }),
    },
    {
      currency_code: "usd",
      amount: faker.number.int({ min: 1500, max: 25000 }),
    },
  ],
  options: [{ value: size }, { value: color }],
  inventory_quantity: faker.number.int({ min: 50, max: 200 }),
  manage_inventory: faker.datatype.boolean(),
});

const generateVariants = () =>
  sizes.flatMap((size) => colors.map((color) => generateVariant(size, color)));

const generateProduct = () => {
  const title = faker.commerce.productName();

  return {
    title,
    subtitle: null,
    description: faker.commerce.productDescription(),
    handle: faker.helpers.slugify(title).toLowerCase(),
    is_giftcard: faker.datatype.boolean(),
    weight: faker.number.int({ min: 100, max: 1000 }),
    images: Array.from({ length: faker.number.int({ max: 5 }) }, () =>
      faker.image.url({ width: 1000, height: 1000 }),
    ),
    options: [
      {
        title: "Size",
        values: sizes,
      },
      {
        title: "Color",
        values: colors,
      },
    ],
    variants: generateVariants(),
  };
};

const NUMBER_OF_PRODUCTS = 1200;

const products = Array.from({ length: NUMBER_OF_PRODUCTS }, generateProduct);

const seed = {
  ...baseSeed,
  products,
};

fs.writeJSONSync("./dev/medusa/data/seed.json", seed, { spaces: 2 });
