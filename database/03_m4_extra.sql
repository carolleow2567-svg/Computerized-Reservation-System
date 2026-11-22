USE computerized_reservation_system;

/* Token for QR */ 
ALTER TABLE bookings
  ADD COLUMN verification_token CHAR(64) NULL UNIQUE;

/* Delete Request */
CREATE TABLE cancellation_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    reason VARCHAR(255),
    request_status ENUM('pending','approved','rejected','refunded')
        NOT NULL DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at DATETIME NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

