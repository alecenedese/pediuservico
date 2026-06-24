-- 📱 Tabela para armazenar tokens de push notification
CREATE TABLE IF NOT EXISTS `push_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(500) NOT NULL,
  `platform` varchar(50) DEFAULT 'expo-android',
  `type` varchar(50) DEFAULT 'expo-push-token',
  `active` tinyint(1) DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 🔗 Se tiver tabela de usuários, adicionar foreign key
-- ALTER TABLE `push_tokens` ADD CONSTRAINT `fk_push_tokens_user` 
-- FOREIGN KEY (`user_id`) REFERENCES `usuario`(`codigo`) ON DELETE CASCADE;
