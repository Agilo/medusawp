import { nonce, root } from "./config";

export interface ConnectRequestBody {
  url: string;
  email: string;
  password: string;
}

interface ConnectErrorResponse {
  message: string;
}

export async function connect(body: ConnectRequestBody) {
  const response = await fetch(`${root}wp/v2/admin/medusa/connect`, {
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

  const error: ConnectErrorResponse = await response.json();

  throw new Error(error.message);
}
