package strath.hospital.model;

import java.time.LocalDateTime;

public class Appointment {
    private int id;
    private int patientId;
    private int staffId;
    private LocalDateTime appointmentTime;
    private String department;
    private String status;
    // getters & setters omitted for brevity
}
