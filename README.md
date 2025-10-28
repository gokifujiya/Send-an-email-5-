# Send-an-Email-5-

This project demonstrates how to send email through **Gmail SMTP** using both:
1. **Postfix** as a local mail transfer agent (MTA) relay, and  
2. **PHPMailer** as an application-level mailer in PHP.

---

## ✉️ Features
- Local mail relay (Postfix) → Gmail SMTP  
- Secure App Password authentication  
- HTML + Plain-text message templates  
- Simple examples using PHP’s `mail()` and PHPMailer  

---

## ⚙️ Setup
1. Install Postfix and configure relay via Gmail:
   ```bash
   sudo apt-get install postfix
   sudo nano /etc/postfix/main.cf
   ```

2. Create /etc/postfix/sasl_passwd:
   ```
   [smtp.gmail.com]:587 your_email@gmail.com:your_app_password
   ```
   Then:
   ```
   sudo chmod 600 /etc/postfix/sasl_passwd
   sudo postmap /etc/postfix/sasl_passwd
   sudo systemctl reload postfix
   ```

3. Create your .env file (not committed):
   ```
   SMTP_HOST=smtp.gmail.com
   SMTP_PORT=587
   SMTP_USERNAME=your_email@gmail.com
   SMTP_PASSWORD=your_16char_app_password
   SMTP_FROM_EMAIL=your_email@gmail.com
   SMTP_FROM_NAME=PHP App
   SMTP_TO_EMAIL=recipient@example.com
   SMTP_TO_NAME=Recipient
   '''

## 🧪 Test
   '''
   php Scripts/mailing-sample.php
   php Scripts/phpmailer-sample.php
   ```
Check your inbox for “Hello World, From PHPMailer!”.
