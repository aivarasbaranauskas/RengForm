INSERT INTO `User` (`id`, `username`, `password`, `email`)
VALUES
  ('5f26cf7f-30a0-11e8-90c6-080027c702a7', 'admin', '$2y$13$YvkFxKWrM.9ulSyWE4msjee/Om3s0qvzXbd9K6bmArhKH2CG6ATeC', 'asdf@asdf.dsf');

INSERT INTO `FormConfig` (`id`, `owner_id`, `title`, `description`, `config`, `created`, `type`)
VALUES
  ('ebe13752-384c-11e8-9074-080027c702a7', '5f26cf7f-30a0-11e8-90c6-080027c702a7', 'selects', '', '[{\"type\":\"radio-group\",\"required\":true,\"label\":\"Radio Group\",\"values\":[{\"label\":\"Option 1\",\"value\":\"option-1\"},{\"label\":\"Option 2\",\"value\":\"option-2\"},{\"label\":\"Option 3\",\"value\":\"option-3\"}],\"name\":\"radio-group\"},{\"type\":\"radio-group\",\"label\":\"Radio Group2\",\"values\":[{\"label\":\"Option 1\",\"value\":\"option-1\"},{\"label\":\"Option 2\",\"value\":\"option-2\"},{\"label\":\"Option 3\",\"value\":\"option-3\"}],\"name\":\"radio-group2\"}]', '2018-04-06 18:58:29', 'simple');
