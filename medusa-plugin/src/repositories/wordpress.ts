import { Repository } from "typeorm";
import { dataSource } from "@medusajs/medusa/dist/loaders/database";

import { Wordpress } from "../models/wordpress";

export class WordpressRepository extends Repository<Wordpress> {}
export default dataSource.getRepository(Wordpress);
