import { faker } from "@faker-js/faker";
import fs from "fs-extra";

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

const productHandles = new Set<string>();

const generateUniqueProductHandle = (title: string): string => {
  const handle =
    faker.helpers.slugify(title).toLowerCase() +
    "-" +
    faker.number.int({ min: 0, max: 1000 });

  if (productHandles.has(handle)) {
    return generateUniqueProductHandle(
      `${title} ${faker.number.int({ min: 2, max: 1000 })}}`,
    );
  }

  productHandles.add(handle);

  return handle;
};

const generateProduct = () => {
  const title = faker.commerce.productName();

  return {
    title,
    subtitle: null,
    description: faker.commerce.productDescription(),
    handle: generateUniqueProductHandle(title),
    is_giftcard: faker.datatype.boolean(),
    weight: faker.number.int({ min: 100, max: 1000 }),
    thumbnail: faker.image.url({ width: 1000, height: 1000 }),
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

// Product Handle,Product Title,Product Subtitle,Product Description,Product Status,Product Thumbnail,Product Weight,Product Length,Product Width,Product Height,Product HS Code,Product Origin Country,Product Material,Product Collection Title,Product Collection Handle,Product Type,Product Tags,Product Discountable,Product Profile Name,Product Profile Type,Variant Title,Variant SKU,Variant Barcode,Variant Inventory Quantity,Variant Manage Inventory,Price USD,Option 1 Name,Option 1 Value,Option 2 Name,Option 2 Value,Sales Channel 1 Name
const csvHeaders = [
  "Product Handle",
  "Product Title",
  "Product Subtitle",
  "Product Description",
  // "Product Status",
  "Product Thumbnail",
  "Product Weight",
  // "Product Length",
  // "Product Width",
  // "Product Height",
  // "Product HS Code",
  // "Product Origin Country",
  // "Product Material",
  // "Product Collection Title",
  // "Product Collection Handle",
  // "Product Type",
  // "Product Tags",
  // "Product Discountable",
  // "Product Profile Name",
  // "Product Profile Type",
  "Variant Title",
  // "Variant SKU",
  // "Variant Barcode",
  "Variant Inventory Quantity",
  "Variant Manage Inventory",
  "Price EUR",
  "Price USD",
  "Option 1 Name",
  "Option 1 Value",
  "Option 2 Name",
  "Option 2 Value",
];

const escapeCsvValue = (value: string | number | boolean | null) => {
  if (value === null) {
    return "";
  }

  if (typeof value === "boolean") {
    return value ? "TRUE" : "FALSE";
  }

  if (typeof value === "number") {
    return value.toString();
  }

  return value.includes(",") ||
    value.includes("http://") ||
    value.includes("https://")
    ? `"${value.replaceAll('"', '\\"')}"`
    : value;
};

const csvRows = products.flatMap((product) => {
  return product.variants.map((variant) => {
    return [
      product.handle,
      product.title,
      product.subtitle,
      product.description,
      // "active",
      product.thumbnail,
      product.weight,
      // null,
      // null,
      // null,
      // null,
      // null,
      // null,
      // null,
      // null,
      // "product",
      // null,
      // true,
      // null,
      // null,
      variant.title,
      // null,
      // null,
      variant.inventory_quantity,
      variant.manage_inventory,
      variant.prices[0].amount,
      variant.prices[1].amount,
      "Size",
      variant.options[0].value,
      "Color",
      variant.options[1].value,
    ].map(escapeCsvValue);
  });
});

const csv = [csvHeaders, ...csvRows].map((row) => row.join(",")).join("\n");

fs.writeFileSync("import-products.csv", csv);
