import { MigrationInterface, QueryRunner } from "typeorm";

export class Wordpress1676902157404 implements MigrationInterface {
  public async up(queryRunner: QueryRunner): Promise<void> {
    await queryRunner.query(
      `CREATE TABLE "wordpress" (
                "id" CHARACTER VARYING NOT NULL PRIMARY KEY,
                "host" CHARACTER VARYING NOT NULL,
                "secret" CHARACTER VARYING NOT NULL,
                "created_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
                "updated_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
                "deleted_at" TIMESTAMP WITH TIME ZONE DEFAULT NULL
            )`,
    );
  }

  public async down(queryRunner: QueryRunner): Promise<void> {
    await queryRunner.query(`DROP TABLE "wordpress"`);
  }
}
