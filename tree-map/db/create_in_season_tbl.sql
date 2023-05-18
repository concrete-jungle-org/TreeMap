--This table is a fixed view of data in the Donat table
--It makes it easier to determine when each fruit is ripe
--and ready to show on tree-map
DROP TABLE IF EXISTS In_Season;

CREATE TABLE In_Season (
  food_id TEXT NOT NULL,
  day_of_year INTEGER NOT NULL,
  week_of_year INTEGER NOT NULL,
  year INTEGER NOT NULL,
  PRIMARY KEY (food_id, day_of_year, year)
);

INSERT OR IGNORE INTO in_season 
SELECT 
  SUBSTRING(food, 3, 17) as food_id, 
  CAST(STRFTIME('%j', date) as INTEGER) as day_of_year,
  CAST(STRFTIME('%W', date) as INTEGER) as week_of_year,
  CAST(STRFTIME('%Y', date) as INTEGER) as year
FROM Donate;

