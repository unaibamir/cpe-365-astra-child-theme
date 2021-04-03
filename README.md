# LearnDash Advanced Compatibility with Paid Memberships Pro
It integrates CPE credits sustem with LearnDash & Paid Memberships Pro. 

### Notes
- This child theme required LearnDash & Paid Memberships & Astra theme.
- Since, it's a custom theme made for a specific project therefore, use it on your own.

### Detail
Once you have configured this child theme, you will see new custom meta boxes under LeanDash courses and Paid Memberships Pro levels admin pages. 

#### LearnDash Course Edit Page:
- Admin can configure CPE credits for the LearnDash courses. Users will require this minimum CPE credits to access/purchase the course.

#### LearnDash Frontend Changes:
- It overrides a few LearnDash template files to customize LearnDash behaviour in blow way.
1) If user does not have course access, it displays a new sidebar explaining on the memberships details to get access to the course. 
2) It displays a custom messages if the user does not have access to course/lesson or quizzes. 

#### LearnDash Custom Shortcodes:
- [cpe_credits_info] This shortcode displays current status of user's CPE credits. How many credits the user have, how many are used and how many are remaining.
- [cpe_user_available_courses] This shortcode displays user available courses bases on user's CPE credits. It displays categories by default, and opening any of it will display its courses. 
- [cpe_user_in_progress_courses] This shortcode displays user's courses in progress based on CPE credits. The display method here is similar as above but it utilizes WordPress's transients to show courses quikly and avoid's LearnDash's core functions. 
- [cpe_user_profile] This shortcode provides an interface to users to customize their profiles. 


#### Paid Memberships Pro Changes:
- It adds a few options for Paid Memberships Pro to integrate CPE credits.
1) It adds an option under levels edit page to confiure CPE credits per membership level and assign it to the user.
2) If a user starts a course which requires certain CPE credits, it deducts the credits from user and maintains its log.
3) If a user wants to leave the course or gain back the used credits, an option is created under profile page. 
4) It also overrides a couple of Paid Memberships Pro templates files to show a modified invoices and orders print versions. 
