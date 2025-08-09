/* ticket item */
SELECT id, title, date_created
  FROM ticket
 WHERE id = :id;