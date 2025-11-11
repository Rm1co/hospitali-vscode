package strath.hospital.model;

import java.time.LocalDateTime;

public class Invoice {
    private int id;
    private int patientId;
    private double total;
    private String status;
    private LocalDateTime createdAt;
    // getters & setters omitted for brevity
}
