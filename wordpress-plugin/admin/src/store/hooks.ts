import * as React from "react";
import { storeContext } from "./context";

export const useStore = () => {
  const store = React.useContext(storeContext);

  return store;
};

export const useConnectedStore = () => {
  const store = useStore();

  if (store.connection === null) {
    throw new Error("Not connected");
  }

  return store;
};

export const useDisconnectedStore = () => {
  const store = useStore();

  if (store.connection !== null) {
    throw new Error("Already connected");
  }

  return store;
};
