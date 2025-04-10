<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session to maintain user state
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
include 'db_connection.php';
include 'createforumusers.php';

// Check connection to the database
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Fetch user ID from session
$user_id = $_SESSION['user_id'];
$class = $_SESSION['user_class'];
$arm = $_SESSION['user_arm'];
$year_enrolled = $_SESSION['student_session'];


// Define function to handle grade-to-point conversion
function gradeToPoint($grade) {
    switch (strtoupper($grade)) {
        case 'A': return 5;
        case 'B': return 4;
        case 'C': return 3;
        case 'D': return 2;
        case 'E': return 1;
        case 'F': return 0;
        default: return 0;
    }
}

// Fetch student details from the database
$student_details = [];
$sql = "SELECT id, name, gender, dob, address, state, class, session, arm FROM students WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->bind_result($student_details['id'], $student_details['name'], $student_details['gender'], 
                       $student_details['dob'], $student_details['address'], $student_details['state'], 
                       $student_details['class'], $student_details['session'], $student_details['arm']);
    $stmt->fetch();
    $stmt->close();
} else {
    die("Failed to fetch student details: " . $conn->error);
}

// Fetch tuckshop balance for the student
$sql = "SELECT vbalance FROM tuck WHERE regno = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->bind_result($student_details['vbalance']);
    $stmt->fetch();
    $stmt->close();
} else {
    die("Failed to fetch tuckshop details: " . $conn->error);
}


// Fetch total number of students in the logged in student class (class peers)
$sql = "SELECT count(id) FROM students WHERE class = ? AND arm = ? AND id != ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("sss", $class, $arm, $user_id);
    $stmt->execute();
    $stmt->bind_result($student_details['count(id)']);
    $stmt->fetch();
    $stmt->close();
} else {
    die("Failed to fetch tuckshop details: " . $conn->error);
}

// Get the logged-in student's ID from the session
$student_id = $_SESSION['user_id'];
// Fetch bursary details for the student
// Modify the query to filter by id_no (student ID number) instead of id
$query = "SELECT ef.*, s.name as sname, s.id_no 
          FROM student_ef_list ef 
          INNER JOIN student s ON s.id = ef.student_id 
          WHERE s.id_no = '$student_id'
          ORDER BY s.name ASC";
$fees = $conn->query($query);

$fees_records = [];
if ($fees->num_rows > 0) {
    while ($row = $fees->fetch_assoc()) {
        // Calculate the total paid amount for this fee record
        $paidQuery = "SELECT SUM(amount) as paid FROM payments WHERE ef_id = " . $row['id'];
        $paidResult = $conn->query($paidQuery);
        $paidData = $paidResult->fetch_array();
        $paid = isset($paidData['paid']) ? $paidData['paid'] : 0;
        $balance = $row['total_fee'] - $paid;
        
        // Add calculated fields to the row array
        $row['paid'] = $paid;
        $row['balance'] = $balance;
        
        // Store the record
        $fees_records[] = $row;
    }
}


 
// Fetch CGPA for each term ==========================
$cgpa_data = [];
$terms = ['1st Term', '2nd Term', '3rd Term'];
foreach ($terms as $term) {
    $sql = "SELECT grade FROM mastersheet WHERE term = ? AND class = ? AND arm = ? AND csession = ? AND id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssss", $term, $student_details['class'], $student_details['arm'], $student_details['session'], $user_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($grade);
        
        $total_grade_points = 0;
        $credit_units = 0;
        while ($stmt->fetch()) {
            $total_grade_points += gradeToPoint($grade);
            $credit_units++;
        }
        
        // Calculate GPA and CGPA
        $gpa = ($credit_units > 0) ? $total_grade_points / $credit_units : 0;
        $cgpa_data[] = ['term' => $term, 'gpa' => round($gpa, 2)];
        
        $stmt->close();
    } else {
        die("Failed to fetch CGPA data for $term: " . $conn->error);
    }
}

// Fetch calendar events
$events = [];
$sql = "SELECT date, title, description FROM calendar";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $formattedDate = date("m/d/Y", strtotime($row['date']));
        $events[$formattedDate] = [
            'title' => $row['title'],
            'description' => $row['description']
        ];
    }
    $result->free();
} else {
    die("Failed to fetch calendar events: " . $conn->error);
}

// PERSONAL AI ===============================================
// Array of AI-styled encouragement messages
$messages = [
    "Hello! I'm here to remind you that every challenge is an opportunity in disguise. Keep pushing forward and embrace your journey of growth.\n\nEvery setback is just a setup for a better comeback. Enjoy the small victories along the way and let your light shine through.",
    "Hi there! Every day is a new chance to make progress and learn something new. Believe in yourself and remember that you're capable of amazing things.\n\nWhen obstacles appear, see them as stepping stones rather than barriers. Your perseverance will guide you to success.",
    "Greetings! Sometimes the road gets tough, but that doesn't mean you aren't making progress. Stay positive and trust that every effort counts.\n\nYour journey is unique and filled with potential. Embrace each moment and let your spirit soar.",
    "Hey friend! Remember that growth comes from overcoming challenges. Every moment you push through is a step toward a brighter future.\n\nTake time to appreciate the progress you've made and know that each day holds new opportunities.",
    "Hello! Your resilience and determination are your greatest strengths. Keep moving forward, even if progress feels slow.\n\nEvery effort you make is a brick in the foundation of your future. Stay encouraged and keep shining.",
    "Hi! Life is a series of ups and downs, but each step builds your strength and wisdom. Trust in your abilities to overcome any challenge.\n\nRemember, every setback teaches you something valuable. Embrace the lessons and keep growing.",
    "Greetings! Every challenge you face is an opportunity to learn and grow. Stay hopeful and let your determination light the way.\n\nKeep your eyes on the prize and trust that every effort brings you closer to success.",
    "Hello there! The journey to success is paved with perseverance and hard work. Every obstacle is a chance to learn and improve.\n\nTake a moment to celebrate your progress. You are capable of overcoming any hurdle with a smile.",
    "Hi! Embrace every challenge as a stepping stone to greatness. Even the smallest steps add up to significant progress over time.\n\nRemember, each experience is a lesson in disguise. Let your inner strength guide you forward.",
    "Hello! You're doing amazing, even if it doesn't always feel that way. Every day is a fresh start full of potential.\n\nStay positive, keep moving forward, and know that every challenge helps shape your success.",
    "Hi friend! When you face challenges, remember that they are opportunities to learn and evolve. Keep your head high and your heart strong.\n\nEvery moment of struggle is a building block for future achievements. Trust in your journey.",
    "Greetings! Your courage and determination inspire those around you. Every challenge is an invitation to grow stronger.\n\nTake pride in every step you take, no matter how small. You're on the path to greatness.",
    "Hey there! Each day offers new opportunities to learn and shine. Trust that every challenge you face is a chance to improve.\n\nKeep moving forward with confidence and let your inner light guide you through every obstacle.",
    "Hello! Believe in the power of small steps, as each one brings you closer to your dreams. Your efforts are always worthwhile.\n\nEmbrace the journey and celebrate the progress you make along the way. You are capable and strong.",
    "Hi! Life's challenges are the best teachers, guiding you to become a better version of yourself. Trust in your ability to grow and learn.\n\nKeep your focus on the positive, and let every experience enrich your life.",
    "Greetings! Every obstacle you overcome is a testament to your strength and perseverance. Stay determined and keep moving forward.\n\nEach day is a fresh opportunity to learn, improve, and become more resilient. Believe in yourself.",
    "Hey friend! Remember that every struggle is a stepping stone to success. Your hard work and determination will pay off in the end.\n\nKeep your spirits high and know that every challenge is a lesson waiting to be discovered.",
    "Hello! Every experience, whether good or challenging, shapes you into a wiser, more capable person. Keep embracing the journey.\n\nBelieve that every setback is a chance to grow stronger and that your future is bright and full of promise.",
    "Hi there! Your journey is filled with valuable lessons and moments of triumph. Embrace every challenge with a positive heart.\n\nKnow that your persistence and resilience are the keys to unlocking a future full of success and happiness.",
    "Greetings! You are capable of overcoming any obstacle that comes your way. Every challenge is an opportunity to learn and grow.\n\nKeep your focus on the progress you've made, and trust that every step forward leads to a brighter tomorrow.",
    "Hello! In every challenge, there is a hidden lesson waiting to be discovered. Stay open to learning and growing through every experience.\n\nYour persistence is the bridge between dreams and reality. Keep believing in yourself.",
    "Hi friend! The challenges you face today will build the strength you need for tomorrow. Keep your spirit high and your focus clear.\n\nEvery struggle is an opportunity to grow. Embrace it with an open heart and let your determination shine.",
    "Greetings! Remember that every day is a new beginning filled with opportunities for growth. Trust in your ability to overcome challenges.\n\nCelebrate your achievements, no matter how small, and let each moment inspire you to keep moving forward.",
    "Hey there! Life may present challenges, but each one is a stepping stone on your path to greatness. Embrace every opportunity to learn and grow.\n\nLet your determination and positive attitude guide you through even the toughest times.",
    "Hello! Every challenge you encounter is a chance to discover more about your inner strength. Keep pushing forward and stay positive.\n\nBelieve that every step, even the smallest, brings you closer to a brighter future. You are resilient and capable.",
    "Hi! Each day offers a fresh opportunity to learn, grow, and become the best version of yourself. Embrace your journey with an open heart.\n\nEvery obstacle you overcome builds the foundation for future success. Keep your focus on your progress.",
    "Greetings! Your unique journey is filled with challenges that help shape your character. Embrace each moment as a learning opportunity.\n\nStay optimistic and trust that every setback paves the way for a stronger comeback.",
    "Hello! When challenges arise, remember that they are simply stepping stones toward your dreams. Keep your spirit uplifted and your mind open.\n\nEvery experience, good or bad, contributes to your growth. Believe in your ability to succeed.",
    "Hi there! Your determination and resilience are your greatest assets. Every challenge you face is an opportunity to evolve and shine brighter.\n\nCelebrate the small wins and learn from each experience. Your journey is a testament to your strength.",
    "Greetings! Life's challenges are invitations to grow, learn, and explore your true potential. Keep a positive outlook and trust in yourself.\n\nEvery step you take, no matter how small, is progress. Let your passion and perseverance guide you to success.",
    "Hello! Each day is a canvas waiting for your unique masterpiece. Embrace every challenge as a chance to add vibrant colors to your journey.\n\nRemember, every brushstroke, no matter how subtle, contributes to a beautiful and evolving story.",
    "Hi friend! When life gets challenging, know that it's molding you into a stronger, wiser individual. Stay curious and open to learning.\n\nEvery experience builds a chapter in your story. Trust that even the toughest moments are stepping stones to success.",
    "Greetings! You are the author of your own success story. Each challenge you face is a plot twist that adds depth and character to your journey.\n\nEmbrace every moment with optimism and let your resilience shine through. Your future is bright and full of potential.",
    "Hey there! Every hurdle you overcome brings you one step closer to your dreams. Keep your eyes on the prize and your heart full of hope.\n\nEach challenge is an opportunity to learn and grow. Trust that your journey is paving the way for amazing achievements.",
    "Hello! Your ability to face challenges head-on is truly inspiring. Every setback is simply a setup for an even greater comeback.\n\nKeep moving forward with determination and let your passion light the way to a future filled with success.",
    "Hi! Remember that every challenge you encounter is a stepping stone to becoming a better version of yourself. Embrace the journey with a positive mindset.\n\nEvery day is an opportunity to learn and evolve. Stay strong, and let your inner light guide you through every obstacle.",
    "Greetings! Life's challenges may seem overwhelming at times, but they are simply part of the path to greatness. Stay resilient and positive.\n\nEach challenge teaches you something valuable. Embrace every lesson and trust in your ability to overcome any obstacle.",
    "Hello friend! Every experience, whether smooth or rocky, adds to the beautiful tapestry of your life. Cherish each moment and keep learning.\n\nYour strength lies in your perseverance. Every challenge is a chance to grow and become even more amazing.",
    "Hi there! Remember that every obstacle is an opportunity to learn, adapt, and shine. Keep your head up and your heart open to new possibilities.\n\nTrust in your ability to transform challenges into triumphs. Your journey is a testament to your resilience.",
    "Greetings! Each challenge you face is a reminder of your strength and potential. Stay focused on your goals and embrace every opportunity to grow.\n\nEvery moment of struggle is a stepping stone to a more fulfilling future. Keep believing in yourself and your dreams.",
    "Hello! Your journey is full of learning experiences that shape who you are. Embrace every challenge as a chance to improve and evolve.\n\nRemember, every small victory contributes to your bigger picture. Keep your heart open and your mind ready to learn.",
    "Hi friend! Every day brings new lessons and opportunities to shine. Trust that even the toughest challenges carry valuable insights.\n\nEmbrace each moment with enthusiasm and let your perseverance drive you toward a brighter tomorrow.",
    "Greetings! Every step you take, no matter how small, builds the foundation for your future. Keep moving forward with confidence and grace.\n\nEach challenge is an opportunity to learn and grow. Believe in yourself, and let your inner light guide your way.",
    "Hey there! When life presents challenges, see them as invitations to become even better. Your journey is a mosaic of growth and learning.\n\nCelebrate your progress and let every setback be a lesson that propels you toward future success.",
    "Hello! Remember that you are capable of amazing things. Every challenge you face is a chance to build strength and wisdom.\n\nEmbrace the journey with a positive mindset and know that every step forward is progress toward your dreams.",
    "Hi! Life is full of ups and downs, and every experience teaches you something new. Stay optimistic and trust in your abilities.\n\nEvery challenge is a stepping stone to success. Keep learning and growing, one day at a time.",
    "Greetings! Each challenge you encounter is a chance to discover more about your strengths and capabilities. Keep pushing forward with determination.\n\nEvery obstacle is an opportunity to learn and improve. Trust in your journey and celebrate every victory, big or small.",
    "Hello friend! Your perseverance in the face of challenges is a source of inspiration. Every day brings new opportunities to learn and evolve.\n\nKeep your heart open and your mind focused on growth. Each experience is a step toward a brighter future.",
    "Hi there! Every obstacle is a chance to learn, adapt, and come out stronger. Embrace each challenge with optimism and courage.\n\nRemember, every effort you make is a testament to your strength. Keep believing in your potential and move forward with confidence.",
    "Greetings! The journey to success is paved with both triumphs and challenges. Every experience helps shape the person you are meant to become.\n\nTake each day as an opportunity to learn and grow. Your resilience and determination will light the way to a bright future.",
    "Hello! Every challenge is a gift that helps you grow in unexpected ways. Embrace each moment with a curious and open heart.\n\nYour journey is filled with valuable lessons. Trust in your ability to learn and let every experience shape your future positively.",
    "Hi friend! Remember that every day is a fresh start full of possibilities. Challenges may come, but each one is a chance to become better.\n\nKeep your spirit high and your mind open. Every step you take, no matter how small, brings you closer to your dreams.",
    "Greetings! Every experience, whether difficult or delightful, adds to the story of your growth. Embrace each challenge as a chance to evolve.\n\nCelebrate your progress and let every obstacle serve as a reminder of your strength and determination.",
    "Hey there! Your ability to overcome challenges is a true reflection of your inner strength. Every setback is merely a stepping stone to success.\n\nStay positive and keep striving, knowing that every experience is an opportunity to learn and grow.",
    "Hello! Each challenge you face is an opportunity to refine your skills and build resilience. Embrace every moment with gratitude.\n\nRemember, every hurdle is a chance to grow stronger. Keep moving forward with optimism and courage.",
    "Hi! Life is a wonderful journey of continuous learning. Every challenge you overcome adds to the tapestry of your experiences.\n\nEmbrace every opportunity to grow and remember that every step forward is a sign of your strength and potential.",
    "Greetings! When challenges arise, view them as invitations to discover more about yourself. Your inner strength is the key to overcoming any obstacle.\n\nKeep your focus on growth and let every experience, both good and challenging, guide you toward success.",
    "Hello friend! Every setback is an opportunity to learn and come back even stronger. Embrace the lessons life offers with an open heart.\n\nTrust in your journey and know that every step you take is a building block for a brighter future.",
    "Hi there! Your journey is filled with ups and downs, and each challenge brings with it valuable lessons. Embrace every moment with positivity.\n\nKeep your determination alive, and trust that every obstacle is an opportunity to grow and shine.",
    "Greetings! Every challenge you face is a chance to tap into your inner resilience and creativity. Keep pushing forward, no matter the odds.\n\nBelieve in yourself and let each experience mold you into the best version of you. Your future is full of promise.",
    "Hello! Embrace every challenge with a smile and an open heart. Each obstacle is a stepping stone on your path to personal greatness.\n\nTake time to celebrate your progress, and remember that every setback is just a setup for a magnificent comeback.",
    "Hi friend! Every experience, even the difficult ones, is a chance to grow and learn. Trust that you are building a strong foundation for the future.\n\nKeep moving forward with confidence, and let your inner light guide you through every challenge.",
    "Greetings! Life is a beautiful journey, filled with opportunities to learn and evolve. Every challenge is a unique lesson waiting to be discovered.\n\nEmbrace each moment and trust in your ability to overcome obstacles with grace and determination.",
    "Hey there! Your resilience in the face of challenges is inspiring. Every setback is a lesson that paves the way for future triumphs.\n\nKeep believing in yourself, and let each experience guide you toward a future filled with growth and success.",
    "Hello! Every day brings new opportunities to learn and improve. Embrace the challenges you face as moments of growth and transformation.\n\nRemember, every obstacle is a chance to build strength. Keep your focus on the positive and trust in your journey.",
    "Hi! Each challenge is a unique opportunity to explore your potential and discover hidden strengths. Stay curious and open to new experiences.\n\nEvery step you take, no matter how small, contributes to the masterpiece of your life. Keep shining and growing.",
    "Greetings! When life presents hurdles, see them as chances to learn and become stronger. Your inner resilience is a guiding light on your journey.\n\nEmbrace every experience with gratitude and trust that every challenge leads to a brighter tomorrow.",
    "Hello friend! Every obstacle you encounter is a chance to refine your skills and build inner strength. Stay positive and keep moving forward.\n\nRemember, each challenge is a stepping stone toward the success and happiness you deserve. Believe in your potential.",
    "Hi there! Every struggle is a valuable lesson in disguise. Embrace each challenge with courage, and let it shape you into a better version of yourself.\n\nTrust in your journey and celebrate every victory, no matter how small. You are capable of amazing things.",
    "Greetings! Every challenge is an invitation to grow, learn, and become more resilient. Keep your heart open and your spirit willing to embrace new lessons.\n\nRemember, every experience builds the foundation for your future success. Stay optimistic and persistent.",
    "Hello! Every day is a fresh opportunity to learn, grow, and evolve. Challenges may come your way, but they only serve to strengthen your resolve.\n\nKeep your focus on your dreams and trust that every step forward, no matter how small, leads to progress.",
    "Hi friend! Your journey is filled with moments of learning and growth. Embrace every challenge as a chance to become even stronger and wiser.\n\nCelebrate your achievements and trust that every obstacle is paving the way for a bright and successful future.",
    "Greetings! Every challenge you overcome adds to the beautiful mosaic of your life. Stay focused, keep learning, and let your inner light shine through.\n\nEach step, even those that feel small, is a move towards a more fulfilling tomorrow. Believe in your path.",
    "Hey there! Life's obstacles are not roadblocks but opportunities to learn and grow. Embrace every challenge with an open mind and a hopeful heart.\n\nTrust that each experience is a valuable lesson, shaping you into a stronger, more capable individual.",
    "Hello! Every moment is a chance to learn and discover new strengths within yourself. Embrace challenges as opportunities for growth and transformation.\n\nKeep your spirit high and your determination strong. Your journey is one of continual progress and discovery.",
    "Hi! Every day brings a fresh opportunity to evolve and improve. The challenges you face are simply lessons that guide you toward success.\n\nKeep believing in yourself, and let every experience add to the tapestry of your achievements.",
    "Greetings! Remember that every challenge is a stepping stone on your journey to greatness. Embrace each obstacle with positivity and a willingness to learn.\n\nEvery moment of struggle is a chance to build resilience. Trust in your ability to grow and achieve your dreams.",
    "Hello friend! Your journey is unique, and every challenge you overcome contributes to your personal growth. Stay determined and open to learning.\n\nEvery setback is an opportunity to come back even stronger. Keep moving forward with a heart full of hope.",
    "Hi there! Each day presents new challenges and opportunities to learn. Embrace every experience with a smile and trust in your ability to overcome obstacles.\n\nRemember, every step forward, no matter how small, is a victory worth celebrating.",
    "Greetings! Every challenge you encounter is a chance to discover more about your inner strength and potential. Embrace each moment with gratitude.\n\nKeep your focus on the positive, and let every experience guide you toward a brighter and more successful future.",
    "Hello! Every day is a new page in your story of growth and success. Embrace the challenges you face as opportunities to write a stronger narrative.\n\nTrust in your ability to overcome obstacles and remember that every experience contributes to your unique journey.",
    "Hi friend! Life is full of twists and turns, and each challenge is a chance to learn something new. Keep your heart open and your mind eager to grow.\n\nEvery setback is just a part of the process. Keep moving forward with hope and resilience.",
    "Greetings! Every experience, even the challenging ones, adds to the richness of your life. Embrace each obstacle as a lesson that makes you wiser.\n\nKeep your spirit high and trust that every challenge leads to a brighter future filled with opportunities.",
    "Hey there! Every challenge is a gateway to new possibilities and personal growth. Embrace the lessons that come with each experience and stay positive.\n\nRemember, your determination and courage will carry you through even the toughest times.",
    "Hello! Each day offers a chance to learn, evolve, and improve. Embrace every challenge with a smile and a willingness to grow.\n\nTrust in your inner strength and know that every experience, whether easy or hard, contributes to your ongoing journey of self-improvement.",
    "Hi! Life is a beautiful journey of ups and downs, where every challenge is a chance to learn and become stronger. Keep your eyes on your goals and your heart full of hope.\n\nEvery step you take is progress. Celebrate your achievements and trust that each obstacle is a stepping stone to greatness.",
    "Greetings! Remember that each challenge is an opportunity to learn and grow. Embrace the journey with a positive attitude and a resilient spirit.\n\nEvery moment of difficulty is a chance to discover more about yourself. Keep moving forward and trust in your ability to overcome any hurdle.",
    "Hello friend! Every challenge you face is a testament to your strength and courage. Embrace each moment with determination and an open heart.\n\nEach experience, whether smooth or rocky, contributes to your personal evolution. Keep believing in your potential and move forward with confidence.",
    "Hi there! Every obstacle is a chance to learn and adapt. Embrace the lessons each challenge offers and let them guide you toward success.\n\nYour journey is a collection of moments that shape you into a stronger, more capable individual. Keep moving forward with optimism.",
    "Greetings! Every challenge you encounter is a valuable lesson in disguise. Embrace each experience with gratitude and a willingness to learn.\n\nRemember that every setback is a setup for an even greater comeback. Trust in your journey and your ability to rise above adversity.",
    "Hello! Each day brings new challenges and opportunities for growth. Embrace every moment with positivity and let your inner strength shine through.\n\nEvery step, no matter how small, is progress. Keep believing in yourself and trust that your journey is one of continuous improvement.",
    "Hi friend! Life is full of unexpected challenges, but every experience is an opportunity to learn and evolve. Keep your heart open to new lessons.\n\nEvery obstacle you overcome builds your resilience. Trust in your ability to grow and embrace the beautiful journey ahead.",
    "Greetings! Remember that every challenge is a chance to discover more about your inner potential. Embrace the process of learning and evolving with every step.\n\nEach experience, whether easy or difficult, adds to the wonderful mosaic of your life. Keep moving forward with courage and optimism.",
    "Hey there! Every challenge you face is an opportunity to build strength and character. Embrace each moment with gratitude and a spirit of learning.\n\nTrust that every setback is a chance to grow and that your journey is paving the way for future success.",
    "Hello! Every day is filled with lessons waiting to be learned. Embrace the challenges you face as opportunities to transform and evolve.\n\nRemember, each experience adds a unique chapter to your story. Keep your heart open and your eyes on the bright horizon ahead.",
    "Hi! Every obstacle is a chance to discover more about your inner resilience and strength. Embrace the challenges with a positive mindset.\n\nEvery step you take, no matter how small, is progress toward a future filled with success. Keep believing in your journey.",
    "Greetings! Life is an adventure filled with challenges and opportunities. Embrace every experience as a chance to learn and grow.\n\nTrust in your inner wisdom and let each challenge shape you into the amazing person you are destined to be.",
    "Hello friend! Every challenge you face is a stepping stone to a brighter, more fulfilling future. Embrace each moment with enthusiasm and an open heart.\n\nRemember, every experience, whether joyful or challenging, contributes to your personal growth and success.",
    "Hi there! Every day is a fresh start, filled with opportunities to learn, evolve, and become better. Embrace the challenges with positivity and confidence.\n\nEvery setback is simply a setup for a greater comeback. Keep your spirits high and your heart full of hope.",
    "Greetings! Every challenge on your path is an opportunity to build strength and gain wisdom. Embrace each moment with a resilient spirit and a positive outlook.\n\nRemember, your journey is filled with endless possibilities. Keep moving forward and trust that every step leads to growth and success."
  ];
  
  $message = $messages[array_rand($messages)];




$student_id = $_SESSION['user_id'];
$student_class = "";
$student_arm = "";
$timetable = [];
$today = date('l'); // Get current day

// Ensure the database connection is valid
if ($conn) {
    // Fetch student class
    $stmt = $conn->prepare("SELECT Class FROM students WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $stmt->bind_result($student_class);
        $stmt->fetch();
        $stmt->close();
    } else {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt = $conn->prepare("SELECT arm FROM students WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $stmt->bind_result($student_arm);
        $stmt->fetch();
        $stmt->close();
    } else {
        die("Error preparing statement: " . $conn->error);
    }

   // Fetch timetable for today only
if (!empty($student_class) && !empty($student_arm)) {
    $stmt = $conn->prepare("SELECT starttime, endtime, subject FROM timetable WHERE class = ? AND arm = ? AND day = ? ORDER BY starttime ASC");
    if ($stmt) {
        $stmt->bind_param("sss", $student_class, $student_arm, $today);
        $stmt->execute();
        $stmt->bind_result($starttime, $endtime, $subject);
        
        while ($stmt->fetch()) {
            $time_slot = date("h:i A", strtotime($starttime)) . " - " . date("h:i A", strtotime($endtime));
            $timetable[] = ['subject' => $subject, 'time' => $time_slot];
        }
        $stmt->close();
    } else {
        die("Error preparing statement: " . $conn->error);
    }
} else {
    die("Database connection error.");
}
}


// Fetch tuck shop transactions for the student
$transactions = [];
$sql = "SELECT productname, units, amount, transactiondate FROM transactiondetails WHERE transactionID = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->bind_result($productname, $units, $amount, $transactiondate);
    
    while ($stmt->fetch()) {
        $transactions[] = [
            'productname' => $productname,
            'units' => $units,
            'amount' => $amount,
            'transactiondate' => $transactiondate
        ];
    }
    $stmt->close();
} else {
    die("Failed to fetch tuck shop transactions: " . $conn->error);
}


// Fetch fetchs students list for class peers 
$classpeer = [];
$sql = "SELECT name, gender, studentmobile, email from students WHERE id != ? and class=? and arm=? and session=? ";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ssss", $user_id, $class, $arm, $year_enrolled);
    $stmt->execute();
    $stmt->bind_result($peername, $gender, $mobile, $email);
    
    while ($stmt->fetch()) {
        $classpeer[] = [
            'name' => $peername,
            'gender' => $gender,
            'studentmobile' => $mobile,
            'email' => $email,
        ];
    }
    $stmt->close();
} else {
    die("Failed to fetch Class Peers: " . $conn->error);
}



//=================== VIEW ASSIGNMENTS // Fetch the class of the logged-in student
$student_id = $_SESSION['user_id'];
$student_class_sql = "SELECT class FROM students WHERE id = '$student_id'";
$student_class_result = $conn->query($student_class_sql);

if ($student_class_result->num_rows == 1) {
    $student_class = $student_class_result->fetch_assoc()['class'];
} else {
    echo "Student class not found.";
    exit();
}

// Fetch assignments for the student's class from the 'assignment' folder
$assignment_files = [];
$assignment_dir = 'assignment/'; // Path to the assignment folder

// Ensure that the directory exists and is readable
if (is_dir($assignment_dir)) {
    // Get all files in the assignment directory
    $files = scandir($assignment_dir);

    // Loop through the files and filter based on the student’s class format
    foreach ($files as $file) {
        // Exclude '.' and '..' from the results
        if ($file !== '.' && $file !== '..') {
            // Extract the class part from the filename, assuming the format is SUBJECT___CLASS
            // Example format: "BIOLOGY___BASIC_4" or "MATH___BASIC_5"

            // Define the expected class format
            $expected_class = str_replace(' ', '_', $student_class); // Convert spaces to underscores, if needed

            // Check if the file name contains the expected class format
            if (stripos($file, '_' . $expected_class) !== false) {
                $assignment_files[] = $file;
            }
        }
    }

    // Display the fetched files (example)
    if (!empty($assignment_files)) {
        //echo "Assignments for your class: <br>";
        foreach ($assignment_files as $assignment) {
            //echo $assignment . "<br>";
        }
    } else {
        //echo "No assignments found for your class.";
    }
} else {
    // echo "The assignment directory does not exist.";
}

// NOTES=========================
// Fetch notes for the student's class from the 'Notes' folder
$notes_files = [];
$notes_dir = 'Notes/'; // Path to the assignment folder

// Ensure that the directory exists and is readable
if (is_dir($notes_dir)) {
    // Get all files in the assignment directory
    $files = scandir($notes_dir);

    // Loop through the files and filter based on the student’s class format
    foreach ($files as $file) {
        // Exclude '.' and '..' from the results
        if ($file !== '.' && $file !== '..') {
            // Extract the class part from the filename, assuming the format is SUBJECT___CLASS
            // Example format: "BIOLOGY___BASIC_4" or "MATH___BASIC_5"

            // Define the expected class format
            $expected_class = str_replace(' ', '_', $student_class); // Convert spaces to underscores, if needed

            // Check if the file name contains the expected class format
            if (stripos($file, '_' . $expected_class) !== false) {
                $notes_files[] = $file;
            }
        }
    }

    // Display the fetched files (example)
    if (!empty($notes_files)) {
        //echo "Notes for your class: <br>";
        foreach ($notes_files as $assignment) {
            //echo $assignment . "<br>";
        }
    } else {
        //echo "No Notes found for your class.";
    }
} else {
    echo "The notes directory does not exist.";
}

// CURRICULUM=====================================
// Fetch Curriculum for the student's class from the 'Curriculum' folder
$curriculum_files = [];
$curriculum_dir = 'Curriculum/'; // Path to the assignment folder

// Ensure that the directory exists and is readable
if (is_dir($curriculum_dir)) {
    // Get all files in the assignment directory
    $files = scandir($curriculum_dir);

    // Loop through the files and filter based on the student’s class format
    foreach ($files as $file) {
        // Exclude '.' and '..' from the results
        if ($file !== '.' && $file !== '..') {
            // Extract the class part from the filename, assuming the format is SUBJECT___CLASS
            // Example format: "BIOLOGY___BASIC_4" or "MATH___BASIC_5"

            // Define the expected class format
            $expected_class = str_replace(' ', '_', $student_class); // Convert spaces to underscores, if needed

            // Check if the file name contains the expected class format
            if (stripos($file, '_' . $expected_class) !== false) {
                $curriculum_files[] = $file;
            }
        }
    }

   
} else {
    // echo "The Curriculum directory does not exist.";
}

// TIMETABLE=========================
 // Fetch timetable if class and arm are found
 if (!empty($student_class) && !empty($student_arm)) {
    $stmt = $conn->prepare("SELECT day, starttime, endtime, subject FROM timetable WHERE class = ? AND arm = ? ORDER BY starttime ASC, FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')");
    if ($stmt) {
        $stmt->bind_param("ss", $student_class, $student_arm);
        $stmt->execute();
        $stmt->bind_result($day, $starttime, $endtime, $subject);
        
        while ($stmt->fetch()) {
            $timeSlot = date("h:i A", strtotime($starttime)) . " - " . date("h:i A", strtotime($endtime));
            $timetable[$timeSlot][$day] = htmlspecialchars($subject);
        }
        $stmt->close();
    } else {
        die("Error preparing statement: " . $conn->error);
    }
}



// STUDENT DEPOSIT============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'fetch') {
    $student_id = $conn->real_escape_string($_POST['id']);

    $sql = "SELECT id, name, class, arm, term, gender, session FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        echo json_encode(null);
    }

    $stmt->close();
    $conn->close();
    exit; // Stop further PHP execution as we've responded with JSON
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    // Retrieve form data with sanitization
    $student_id = htmlspecialchars($_POST['id']);
    $name = htmlspecialchars($_POST['name']);
    $class = htmlspecialchars($_POST['class']);
    $arm = htmlspecialchars($_POST['arm']);
    $term = htmlspecialchars($_POST['term']);
    $gender = htmlspecialchars($_POST['gender']);
    $session = htmlspecialchars($_POST['session']);
    $depositor_name = htmlspecialchars($_POST['depositor_name']);
    $depositor_mobile = htmlspecialchars($_POST['depositor_mobile']);
    $amount_deposited = filter_var($_POST['amount_deposited'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $currdate=date("Ymd_His");

    // Validate amount
    if (!is_numeric($amount_deposited) || $amount_deposited <= 0) {
        die("Invalid amount deposited.");
    }

    // File upload handling
    $target_dir = "bursary/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true); // Create directory if it doesn't exist
    }

    $original_extension = pathinfo($_FILES["file_upload"]["name"], PATHINFO_EXTENSION);
    $file_name = date("Ymd_His") . "." . $original_extension;
    $file_path = $target_dir . str_replace("/", "_", $student_id) . "_" . $file_name;
    $file = str_replace("/", "_", $student_id) . "_" . $file_name;

    // Check file type and size
    $allowed_types = ['jpg', 'jpeg'];
    if (!in_array(strtolower($original_extension), $allowed_types)) {
        die("Invalid file type. Only JPG and JPEG are allowed.");
    }

    if ($_FILES['file_upload']['size'] > 2 * 1024 * 1024) { // Limit file size to 2MB
        die("File size exceeds 2MB.");
    }

    if (move_uploaded_file($_FILES["file_upload"]["tmp_name"], $file_path)) {
        // File was successfully uploaded
        echo "The file has been uploaded at " . date("Y-m-d H:i:s") . ".<br>";

        // SQL to insert data into the database
        $sql = "INSERT INTO prebursary (id, name, class, arm, term, gender, session, depositor, mobile, amount, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssds", $student_id, $name, $class, $arm, $term, $gender, $session, $depositor_name, $depositor_mobile, $amount_deposited, $file);

        if ($stmt->execute()) {
            echo "New record created successfully";
        } else {
            echo "Error: " . htmlspecialchars($stmt->error);
        }

        $stmt->close();
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

// PAYEMNT STATUS====================
// Fetch all students for display where id equals the logged in student id,
// ordering the most recent records (by date) at the top.
$students = $conn->query("SELECT * FROM prebursary WHERE id = '" . $_SESSION['user_id'] . "' ORDER BY date DESC");


// STUDENT ACADEMIC CHART ===============================

// Get distinct sessions for the student
$sql = "SELECT DISTINCT csession FROM mastersheet WHERE id = ? ORDER BY csession";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($csession);
$sessions = array();
while ($stmt->fetch()) {
    $sessions[] = $csession;
}
$stmt->close();



// Define the terms
$terms = ['1st Term', '2nd Term', '3rd Term'];

// Fetch total scores per term for each session
// Fetch GPA per term for each session
$session_data = [];
foreach ($sessions as $session) {
    $gpas = [];
    foreach ($terms as $term) {
       $sql = "SELECT grade FROM mastersheet WHERE term = ? AND id = ? AND csession = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $term, $user_id, $session);
        $stmt->execute();
        $stmt->bind_result($grade);
        $rows = [];
        while ($stmt->fetch()) {
            $rows[] = ['grade' => $grade];
        }
        $stmt->close();


        $total_grade_points = 0;
        $credit_units = 0;
        foreach ($rows as $row) {
            $total_grade_points += gradeToPoint($row['grade']); // Ensure this function is defined
            $credit_units++;
        }
        $gpa = ($credit_units > 0) ? $total_grade_points / $credit_units : 0;
        $gpas[] = round($gpa, 2);
    }
    $session_data[$session] = $gpas;
}

// Generate JSON for JavaScript
$termsJson = json_encode($terms);
$datasetsJson = json_encode(array_map(function($session) use ($session_data) {
    return [
        'label' => $session,
        'data' => $session_data[$session]
    ];
}, $sessions));

// Generate JSON for JavaScript
$termsJson = json_encode($terms);
$datasetsJson = json_encode(array_map(function($session) use ($session_data) {
    return [
        'label' => $session,
        'data' => $session_data[$session]
    ];
}, $sessions));








// Fetch the logged-in student's name
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name FROM students WHERE id=?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($student_name);
$stmt->fetch();
$stmt->close();



// Close database connection
// $conn->close();
?>