# AI Summary Setup

The weekly logbook summary is available to teacher accounts and only accepts students assigned to the signed-in teacher.

1. Create a Gemini API key in the Google AI Studio account used by the school.
2. Add the key to the local `.env` file. Do not commit the key or place it in frontend JavaScript:

   ```env
   GEMINI_API_KEY=your-key
   GEMINI_MODEL=gemini-2.5-flash
   ```

3. If Laravel config is cached, run `php artisan config:clear`.
4. Assign students to their teachers from Admin > Manajemen Akun.
5. Sign in as a teacher, choose an assigned student and the first date of the seven-day period, then request the summary.

Before each request, the teacher must confirm the disclosure shown in the dashboard. The integration sends the selected logbooks' dates, statuses, and activity text to Google Gemini; it does not send student names, email addresses, or photos. Common email, Indonesian mobile number, and 16-digit NIK patterns are redacted before sending. This pattern-based redaction is not exhaustive, so the teacher must review entries and avoid sending unnecessary sensitive details.

The school should inform users about this third-party processing, obtain any required consent, and confirm that the provider's data handling matches its privacy policy. The prompt tells the model to treat logbook content as data rather than instructions, but this is not a guarantee against prompt injection. AI output is advisory and must be reviewed by the teacher.