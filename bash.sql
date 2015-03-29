CREATE TABLE IF NOT EXISTS "bc_quotes" (
	"id" serial PRIMARY KEY,
	"timestamp" integer NOT NULL,
	"ip" varchar(255) NOT NULL,
	"quote" text NOT NULL,
	"active" boolean NOT NULL,
	"popularity" integer NOT NULL
);

CREATE TABLE IF NOT EXISTS "bc_votes" (
	"id" serial PRIMARY KEY,
	"quote_id" integer NOT NULL,
	"ip" varchar(255) NOT NULL,
	"type" integer NOT NULL
);
