package strath.hospital.controller;

import java.io.IOException;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import strath.hospital.dao.PatientDAO;

@WebServlet(name = "PatientController", urlPatterns = { "/api/patients" })
public class PatientController extends HttpServlet {
    protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        // TODO: implement call to DAO and respond with JSON
        resp.setContentType("application/json");
        resp.getWriter().write("[]");
    }

}

@PostMapping("/register")
public Map<String, Object> register(@RequestBody Map<String, String> request) {
    String name = request.get("name");
    String email = request.get("email");
    String password = request.get("password");
    // TODO: Save to DB
    return Map.of("message", "Registered successfully", "name", name, "email", email);
}
