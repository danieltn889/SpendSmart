**SpendSmart Description:**
1. SpendSmart: Personal Stock and Expense Management System**:
   - SpendSmart helps people keep track of their things and money using their phone. It's like a special text messaging app.
2. System Description:
   - SpendSmart has three parts: managing stuff, tracking spending, and changing account settings.
     - Managing Stuff: Add, change, or remove things you own.
     - Tracking Spending: See how much money you spend on things.
     - Changing Account Settings: Change your password or email address.
3. Purpose:
   - SpendSmart wants to make it easier for people to manage their things and money, especially using simple phone messages.
4. Usage:
   - People can use SpendSmart for themselves or their small businesses, all from their phone.
5. Support and Contributions:
   - People can help make SpendSmart better by reporting problems or suggesting ideas on GitHub.

**How to Run SpendSmart**

Follow these steps to set up and run the SpendSmart project:

1. Download the ZIP File:
   - Download the SpendSmart project ZIP file from the repository.
2. Extract and Copy:
   - Extract the ZIP file and copy the `spendsmart` folder.
3. Paste Inside Root Directory:
   - Paste the `spendsmart` folder inside the root directory of your web server:
     - For XAMPP: `xampp/htdocs`
     - For WAMP: `wamp/www`
     - For LAMP: `var/www/html`
4. Open PHPMyAdmin:
   - Open your web browser and navigate to PHPMyAdmin at http://localhost/phpmyadmin.
5. Create Database:
   - Create a new database named `spendsmartdb`.
6. Import Database:
   - Inside the ZIP package, locate the `spendsmartdb.sql` file within the `SQL file` folder.
   - Import this SQL file into the `spendsmartdb` database you created.
   - 
**How to Configure Simulation with African Stalking:**

1. Register for African Stalking Account:
   - Sign up on the African Stalking website.
2. Access Dashboard:
   - Log in to your African Stalking account.
3. Set Up USSD Simulator:
   - Find the USSD test tool.
4. Create Short Codes:
   - Make special codes for testing.
5. Configure Callback URLs:
   - Make sure SpendSmart can talk to African Stalking.
6. Launch Simulation:
   - Start the pretend phone test.
7. Test the Simulation:
   - Try using SpendSmart like you would on a real phone.
**Files to access to use USSD:**
   -ussdapplication.php
**To be reguster by message code are on :**
   -incincomingsms.php
