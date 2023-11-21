import { nonce, root } from "./config";

export interface DisconnectRequestBody {
  force?: boolean;
}

export type Disconnect200Response = null;
export interface Disconnect400ErrorResponse {
  medusa_disconnect_failed: true;
  message: string;
}

export class MedusaDisconnectError extends Error {
  constructor(message: string) {
    super(message);
    this.name = "MedusaDisconnectError";
  }
}

export async function disconnect(body?: DisconnectRequestBody) {
  const response = await fetch(`${root}wp/v2/admin/medusa/disconnect`, {
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
    return;
  }

  if (response.status === 400) {
    const error: Disconnect400ErrorResponse = await response.json();

    if (error.medusa_disconnect_failed) {
      throw new MedusaDisconnectError(error.message);
    }

    throw new Error(error.message);
  }

  throw new Error("Unknown error");
}
