-- Initialize databases for Tutorial 2
CREATE DATABASE IF NOT EXISTS ticket;
CREATE DATABASE IF NOT EXISTS ticket_test;

-- Grant permissions (if needed)
GRANT ALL PRIVILEGES ON ticket.* TO 'root'@'%';
GRANT ALL PRIVILEGES ON ticket_test.* TO 'root'@'%';
FLUSH PRIVILEGES;