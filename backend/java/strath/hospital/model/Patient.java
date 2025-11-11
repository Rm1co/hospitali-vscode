package strath.hospital.model;

import java.time.LocalDate;

public class Patient {
    private int id;
    private String firstName;
    private String lastName;
    private LocalDate dob;
    private String gender;
    private String phone;

    // getters and setters
    public int getId(){return id;}
    public void setId(int id){this.id = id;}
    public String getFirstName(){return firstName;}
    public void setFirstName(String f){this.firstName = f;}
    public String getLastName(){return lastName;}
    public void setLastName(String l){this.lastName = l;}
    public LocalDate getDob(){return dob;}
    public void setDob(LocalDate d){this.dob = d;}
    public String getGender(){return gender;}
    public void setGender(String g){this.gender = g;}
    public String getPhone(){return phone;}
    public void setPhone(String p){this.phone = p;}
}
