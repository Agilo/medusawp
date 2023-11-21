import { NextFunction, Request, Response } from "express";

import { isValidUrl } from "../utils/url";

export async function validateOriginHeader(
  req: Request,
  res: Response,
  next: NextFunction,
) {
  const origin = req.get("origin");

  if (!isValidUrl(origin)) {
    return res.status(403).json({
      code: 403,
      message: "Access denied.",
    });
  }

  next();
}
