import * as React from "react";
import { ConnectRequestBody } from "api/connect";

export type StoreContextValue =
  | {
      connection: null;
      connect: (data: ConnectRequestBody) => Promise<void>;
    }
  | {
      connection: {
        url: string;
        email: string;
      };
      disconnect: () => Promise<void>;
    };

export const storeContext = React.createContext<StoreContextValue>({
  connection: null,
  connect: async () => {
    throw new Error("Not implemented");
  },
});
