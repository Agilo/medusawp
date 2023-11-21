// eslint-disable-next-line @typescript-eslint/no-unused-vars
import type * as CSS from "csstype";

declare module "csstype" {
  interface Properties {
    [index: `--${string}`]: string | number | undefined;
  }
}
