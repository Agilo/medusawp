import { BeforeInsert, Column, Entity } from "typeorm";
import { SoftDeletableEntity } from "@medusajs/medusa";
import { generateEntityId } from "@medusajs/medusa/dist/utils";

/**
 * @schema WordPress
 * title: "WordPress"
 * description: "WordPress model is an entity representing a connection to a WordPress instance"
 * type: "object"
 * required:
 *   - id
 *   - created_at
 *   - updated_at
 *   - deleted_at
 *   - host
 *   - secret
 * properties:
 *   id:
 *     type: string
 *     description: "The WordPress connection DB entity id"
 *     example: wordpress_01G1G5V26F5TB3GPAPNJ8X1S3V
 *   created_at:
 *     description: The date with timezone at which the resource was created.
 *     type: string
 *     format: date-time
 *   updated_at:
 *     description: The date with timezone at which the resource was updated.
 *     type: string
 *     format: date-time
 *   deleted_at:
 *     description: The date with timezone at which the resource was deleted.
 *     nullable: true
 *     type: string
 *     format: date-time
 *   host:
 *     type: string
 *     description: "The WordPress connection host"
 *     example: https://example.com
 *   secret:
 *     type: string
 *     description: "The WordPress connection secret used for authentication"
 */

@Entity()
export class Wordpress extends SoftDeletableEntity {
  @Column({ type: "varchar" })
  host!: string;

  @Column({ type: "varchar" })
  secret!: string;

  @BeforeInsert()
  private beforeInsert(): void {
    this.id = generateEntityId(this.id, "wordpress");
  }
}
