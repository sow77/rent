-- Primero eliminamos las traducciones existentes
DELETE FROM translations WHERE entity_type IN ('boat', 'transfer');

-- Traducciones para barcos
INSERT INTO translations (entity_type, entity_id, language, name, description, features) VALUES
-- Yate de Lujo
('boat', 4, 'fr', 'Yacht de Luxe', 'Yacht de luxe avec tout le confort', '["Cuisine complète", "Chambres", "Salle de bain privée", "Terrasse"]'),
('boat', 4, 'en', 'Luxury Yacht', 'Luxury yacht with all amenities', '["Full Kitchen", "Bedrooms", "Private Bathroom", "Terrace"]'),
('boat', 4, 'de', 'Luxusyacht', 'Luxuriöse Yacht mit allen Annehmlichkeiten', '["Vollküche", "Schlafzimmer", "Privatbad", "Terrasse"]'),

-- Velero Clásico
('boat', 5, 'fr', 'Voilier Classique', 'Voilier classique pour une navigation tranquille', '["Cuisine basique", "Chambres", "Salle de bain"]'),
('boat', 5, 'en', 'Classic Sailboat', 'Classic sailboat for peaceful sailing', '["Basic Kitchen", "Bedrooms", "Bathroom"]'),
('boat', 5, 'de', 'Klassisches Segelboot', 'Klassisches Segelboot für ruhiges Segeln', '["Einfache Küche", "Schlafzimmer", "Badezimmer"]'),

-- Lancha Rápida
('boat', 6, 'fr', 'Bateau Rapide', 'Bateau rapide pour les aventures', '["Sièges confortables", "Équipement de sécurité"]'),
('boat', 6, 'en', 'Speed Boat', 'Fast boat for adventures', '["Comfortable Seats", "Safety Equipment"]'),
('boat', 6, 'de', 'Schnellboot', 'Schnelles Boot für Abenteuer', '["Bequeme Sitze", "Sicherheitsausrüstung"]');

-- Traducciones para transfers
INSERT INTO translations (entity_type, entity_id, language, name, description, features) VALUES
-- Limusina Ejecutiva
('transfer', 1, 'fr', 'Limousine Exécutive', 'Service de transfert exécutif', '["WiFi", "Boissons", "Chauffeur professionnel"]'),
('transfer', 1, 'en', 'Executive Limousine', 'Executive transfer service', '["WiFi", "Drinks", "Professional Driver"]'),
('transfer', 1, 'de', 'Executive Limousine', 'Executive Transfer-Service', '["WLAN", "Getränke", "Professioneller Fahrer"]'),

-- Minivan Familiar
('transfer', 2, 'fr', 'Minivan Familial', 'Transfert familial spacieux', '["Sièges confortables", "Climatisation"]'),
('transfer', 2, 'en', 'Family Minivan', 'Spacious family transfer', '["Comfortable Seats", "Air Conditioning"]'),
('transfer', 2, 'de', 'Familien-Minivan', 'Geräumiger Familientransfer', '["Bequeme Sitze", "Klimaanlage"]'),

-- SUV Premium
('transfer', 3, 'fr', 'SUV Premium', 'Transfert en SUV de luxe', '["WiFi", "Boissons", "Chauffeur professionnel"]'),
('transfer', 3, 'en', 'Premium SUV', 'Luxury SUV transfer', '["WiFi", "Drinks", "Professional Driver"]'),
('transfer', 3, 'de', 'Premium SUV', 'Luxus-SUV-Transfer', '["WLAN", "Getränke", "Professioneller Fahrer"]'); 