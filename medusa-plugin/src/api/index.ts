import { Router } from "express";

import wordpress from "./wordpress";

export default (rootDirectory: string, options: unknown) => {
  const router = Router();

  router.use("/wordpress", wordpress(rootDirectory, options));

  return router;
};
