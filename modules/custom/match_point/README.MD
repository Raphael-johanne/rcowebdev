# Todo after install

ALTER TABLE match_point_winner ADD CONSTRAINT fk_match_point_user_winner FOREIGN KEY (user_id) REFERENCES match_point_user(id);
