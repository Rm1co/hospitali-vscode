package strath.hospital.dao;

import strath.hospital.model.Patient;
import strath.hospital.util.DatabaseConfig;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class PatientDAO {
    public List<Patient> findAll() throws SQLException {
        List<Patient> list = new ArrayList<>();
        try (Connection c = DatabaseConfig.getConnection();
             PreparedStatement ps = c.prepareStatement("SELECT * FROM patients");
             ResultSet rs = ps.executeQuery()) {
            while(rs.next()){
                Patient p = new Patient();
                p.setId(rs.getInt("id"));
                p.setFirstName(rs.getString("first_name"));
                p.setLastName(rs.getString("last_name"));
                // ... other fields
                list.add(p);
            }
        }
        return list;
    }
}
