import * as React from "react";
import { StoreContextValue, storeContext } from "./context";
import { useMutation } from "@tanstack/react-query";
import { ConnectRequestBody, connect } from "api/connect";
import { disconnect } from "api/disconnect";

const getInitialConnection = (): StoreContextValue["connection"] => {
  // uncomment this to test the connection
  // return {
  //   url: "http://www.red.com",
  //   email: "admin@red.com",
  // };

  // or uncomment this to test when there is no connection
  // return null;

  if (
    "medusawp" in window &&
    typeof window.medusawp === "object" &&
    window.medusawp !== null &&
    "connection" in window.medusawp &&
    typeof window.medusawp.connection === "object" &&
    window.medusawp.connection !== null &&
    "url" in window.medusawp.connection &&
    typeof window.medusawp.connection.url === "string" &&
    "email" in window.medusawp.connection &&
    typeof window.medusawp.connection.email === "string"
  ) {
    return {
      url: window.medusawp.connection.url,
      email: window.medusawp.connection.email,
    };
  }

  return null;
};

export const StoreProvider: React.FC<{ children?: React.ReactNode }> = ({
  children,
}) => {
  const [connection, setConnection] = React.useState<
    StoreContextValue["connection"]
  >(getInitialConnection());

  const connectMutation = useMutation(["medusawp", "wp", "connect"], connect, {
    onSuccess(_, variables) {
      setConnection({
        url: variables.url,
        email: variables.email,
      });
    },
  });

  const disconnectMutation = useMutation(
    ["medusawp", "wp", "disconnect"],
    disconnect,
    {
      onSuccess() {
        setConnection(null);
      },
    },
  );

  const value = React.useMemo(() => {
    if (connection) {
      return {
        connection,
        disconnect: () => disconnectMutation.mutateAsync(undefined),
      };
    }

    return {
      connection: null,
      connect: (data: ConnectRequestBody) => connectMutation.mutateAsync(data),
    };
  }, [connection, disconnectMutation, connectMutation]);

  return (
    <storeContext.Provider value={value}>{children}</storeContext.Provider>
  );
};
