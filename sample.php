
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Library Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            box-sizing: border-box;
        }
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .library-bg {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.4)), 
                        url('https://images.unsplash.com/photo-1481627834876-b7833e8f5570?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2028&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .book-card:hover {
            transform: translateY(-2px);
            transition: transform 0.2s ease;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Registration Page -->
    <div id="registrationPage" class="library-bg min-h-screen flex items-center justify-center px-4 hidden">
        <div class="bg-white bg-opacity-90 backdrop-blur-sm rounded-2xl shadow-2xl p-8 w-full max-w-md">
            <div class="text-center mb-8">
                <div class="text-6xl mb-4">📚</div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Create Account</h1>
                <p class="text-gray-600">Join University Library</p>
            </div>
            
            <form onsubmit="handleRegistration(event)" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <input type="text" id="regFullName" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Enter your full name">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input type="email" id="regEmail" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Enter your email">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Student/Employee ID</label>
                    <input type="text" id="regId" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Enter your ID number">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select id="regRole" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="">Select your role</option>
                        <option value="Student">Student</option>
                        <option value="Teacher">Teacher</option>
                        <option value="Librarian">Librarian</option>
                        <option value="Staff">Staff</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <input type="tel" id="regPhone" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Enter your phone number">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="password" id="regPassword" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Create a password">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                    <input type="password" id="regConfirmPassword" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Confirm your password">
                </div>
                
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="agreeTerms" required class="rounded">
                    <label for="agreeTerms" class="text-sm text-gray-700">I agree to the library terms and conditions</label>
                </div>
                
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-medium transition-colors">
                    Create Account
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-sm text-white text-opacity-90 drop-shadow">Already have an account?</p>
                <button onclick="showLoginPage()" class="text-blue-200 hover:text-blue-100 font-medium text-sm transition-colors drop-shadow">
                    Sign In
                </button>
            </div>
            
            <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                <p class="text-xs text-blue-800 mb-2"><strong>Note:</strong> Account registration requires approval.</p>
                <p class="text-xs text-blue-700">After submitting, please visit the library with a valid ID for account verification and activation.</p>
            </div>
        </div>
    </div>

    <!-- Login Page -->
   <div id="loginPage" class="library-bg min-h-screen flex items-center justify-center px-4">
    <div class="bg-white bg-opacity-90 backdrop-blur-sm rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <div class="text-6xl mb-4">📚</div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome Back</h1>
            <p class="text-gray-600">Sign in to your account</p>
        </div>

        <form onsubmit="handleLogin(event)" class="space-y-6">
            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email/Username</label>
                <input type="text" id="loginEmail" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    placeholder="Enter your email or username">
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="password" id="loginPassword" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    placeholder="Enter your password">
            </div>

            <!-- Role Dropdown -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                <select id="userRole" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    <option value="" disabled selected>Select your role</option>
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                    <option value="librarian">Librarian</option>
                    <option value="staff">Staff</option>
                </select>
            </div>

            <!-- Buttons -->
            <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-medium transition-colors">
                Sign In
            </button>

            <button type="button" onclick="showForgotPassword()"
                class="w-full text-blue-600 hover:text-blue-800 py-2 text-sm transition-colors">
                Forgot password?
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-700">Don't have an account?</p>
            <button onclick="showRegistrationPage()"
                class="text-blue-600 hover:text-blue-800 font-medium text-sm transition-colors">
                Create new account
            </button>
        </div>
    </div>
</div>


    <!-- Main Application -->
    <div id="mainApp" class="hidden">
        <!-- Header -->
        <header class="bg-white shadow-lg border-b-4 border-blue-600">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center space-x-3">
                        <div class="text-3xl">📚</div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">University Library</h1>
                            <p class="text-sm text-gray-600">Management System</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-sm text-gray-600">
                            Welcome, <span id="userName" class="font-semibold"></span>
                            <span id="userRole" class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full ml-2"></span>
                        </div>
                        <button onclick="logout()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div id="navMenu" class="flex space-x-8 py-3">
                    <!-- Navigation will be populated based on role -->
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Student Dashboard -->
            <div id="studentDashboard" class="dashboard hidden">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">Student Dashboard</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Current Borrowed Books -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">📚 My Borrowed Books (2/3)</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Introduction to Computer Science</h4>
                                        <p class="text-sm text-gray-600">by Thomas H. Cormen</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-red-600">Due: Dec 20, 2024</p>
                                        <p class="text-xs text-gray-500">2 days left</p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Data Structures and Algorithms</h4>
                                        <p class="text-sm text-gray-600">by Robert Sedgewick</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-green-600">Due: Dec 25, 2024</p>
                                        <p class="text-xs text-gray-500">7 days left</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="space-y-6">
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                            <button onclick="showSection('bookInventory')" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-medium mb-3 transition-colors">
                                🔖 Reserve Book
                            </button>
                            <button onclick="showSection('searchBooks')" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-medium transition-colors">
                                🔍 Search Books
                            </button>
                        </div>
                        
                        <!-- Penalty Status -->
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">💰 Penalty Status</h3>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-green-600 mb-2">₱0.00</div>
                                <p class="text-sm text-gray-600">No outstanding penalties</p>
                                <div class="mt-4 p-3 bg-green-50 rounded-lg">
                                    <p class="text-sm text-green-800">✅ Account in good standing</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Teacher Dashboard -->
            <div id="teacherDashboard" class="dashboard hidden">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">Teacher Dashboard</h2>
                
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8">
                    <div class="flex">
                        <div class="text-yellow-400 text-xl mr-3">⚠️</div>
                        <div>
                            <p class="text-yellow-800 font-medium">Semester End Reminder</p>
                            <p class="text-yellow-700 text-sm">Please return all borrowed books at the end of the semester for clearance.</p>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">📚 My Borrowed Books (5)</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Advanced Mathematics</h4>
                                        <p class="text-sm text-gray-600">by James Stewart</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-green-600">Due: Jan 15, 2025</p>
                                        <p class="text-xs text-gray-500">28 days left</p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Teaching Methodologies</h4>
                                        <p class="text-sm text-gray-600">by Robert Marzano</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-green-600">Due: Jan 20, 2025</p>
                                        <p class="text-xs text-gray-500">33 days left</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Clearance Status</h3>
                            <div class="text-center">
                                <div class="text-4xl mb-3">✅</div>
                                <p class="text-green-600 font-medium">Cleared for Current Semester</p>
                                <p class="text-sm text-gray-600 mt-2">All requirements met</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Librarian Dashboard -->
            <div id="librarianDashboard" class="dashboard hidden">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900">Librarian Dashboard</h2>
                    <button onclick="showAddBookModal()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        ➕ Add New Book
                    </button>
                </div>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Books</p>
                                <p class="text-3xl font-bold text-gray-900">15,247</p>
                            </div>
                            <div class="text-4xl">📚</div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Available</p>
                                <p class="text-3xl font-bold text-gray-900">12,891</p>
                            </div>
                            <div class="text-4xl">✅</div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Borrowed</p>
                                <p class="text-3xl font-bold text-gray-900">2,156</p>
                            </div>
                            <div class="text-4xl">📖</div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Archived</p>
                                <p class="text-3xl font-bold text-gray-900">200</p>
                            </div>
                            <div class="text-4xl">📦</div>
                        </div>
                    </div>
                </div>

                <!-- Books Table -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Book Inventory</h3>
                        <input type="text" placeholder="Search books..." class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">Introduction to Computer Science</div>
                                        <div class="text-sm text-gray-500">ISBN: 978-0-262-03384-8</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Thomas H. Cormen</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">Borrowed</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <button class="text-blue-600 hover:text-blue-900">Edit</button>
                                        <button class="text-yellow-600 hover:text-yellow-900">Archive</button>
                                        <button class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">Data Structures and Algorithms</div>
                                        <div class="text-sm text-gray-500">ISBN: 978-0-321-57351-3</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Robert Sedgewick</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Available</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <button class="text-blue-600 hover:text-blue-900">Edit</button>
                                        <button class="text-yellow-600 hover:text-yellow-900">Archive</button>
                                        <button class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Staff Dashboard -->
            <div id="staffDashboard" class="dashboard hidden">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">Staff Dashboard</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Facilitate Borrowing -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">📝 Facilitate Borrowing</h3>
                        <div class="space-y-4">
                            <input type="text" placeholder="Student/Teacher ID" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <input type="text" placeholder="Book ISBN or Title" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <button class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-medium transition-colors">
                                Process Borrowing
                            </button>
                        </div>
                    </div>
                    
                    <!-- Facilitate Returning -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">📥 Facilitate Returning</h3>
                        <div class="space-y-4">
                            <input type="text" placeholder="Book ISBN or Transaction ID" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div class="flex items-center space-x-2">
                                <input type="checkbox" id="damaged" class="rounded">
                                <label for="damaged" class="text-sm text-gray-700">Book damaged</label>
                            </div>
                            <button class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-medium transition-colors">
                                Process Return
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Borrower Status Search -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">👤 View Borrower Status</h3>
                    <div class="flex space-x-4 mb-6">
                        <input type="text" placeholder="Search by ID or Name" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                            Search
                        </button>
                    </div>
                    
                    <!-- Sample Borrower Info -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h4 class="font-medium text-gray-900">John Smith (Student)</h4>
                                <p class="text-sm text-gray-600">ID: STU2024001</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-red-600">Penalty: ₱50.00</p>
                                <button class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded mt-1">Mark as Paid</button>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span>Introduction to Computer Science</span>
                                <span class="text-red-600">Overdue (2 days)</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span>Data Structures and Algorithms</span>
                                <span class="text-green-600">Due in 7 days</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Book Inventory Page -->
            <div id="bookInventory" class="section hidden">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">Book Inventory</h2>
                
                <!-- Search and Filters -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <input type="text" placeholder="Search books by title, author, or ISBN..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option>All Status</option>
                            <option>Available</option>
                            <option>Reserved</option>
                            <option>Borrowed</option>
                            <option>Archived</option>
                        </select>
                        <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option>All Categories</option>
                            <option>Computer Science</option>
                            <option>Mathematics</option>
                            <option>Literature</option>
                            <option>Science</option>
                        </select>
                    </div>
                </div>

                <!-- Books Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="book-card bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-start space-x-4">
                            <div class="w-16 h-20 bg-gradient-to-b from-blue-400 to-blue-600 rounded flex items-center justify-center text-white font-bold text-xs">CS</div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 mb-1">Introduction to Computer Science</h3>
                                <p class="text-sm text-gray-600 mb-2">by Thomas H. Cormen</p>
                                <div class="flex items-center space-x-2 mb-3">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Available</span>
                                    <span class="text-xs text-gray-500">Computer Science</span>
                                </div>
                                <button class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg text-sm font-medium transition-colors">
                                    Borrow Book
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="book-card bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-start space-x-4">
                            <div class="w-16 h-20 bg-gradient-to-b from-red-400 to-red-600 rounded flex items-center justify-center text-white font-bold text-xs">MATH</div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 mb-1">Advanced Mathematics</h3>
                                <p class="text-sm text-gray-600 mb-2">by James Stewart</p>
                                <div class="flex items-center space-x-2 mb-3">
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">Reserved</span>
                                    <span class="text-xs text-gray-500">Mathematics</span>
                                </div>
                                <button class="w-full bg-yellow-600 hover:bg-yellow-700 text-white py-2 rounded-lg text-sm font-medium transition-colors">
                                    Join Queue
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="book-card bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-start space-x-4">
                            <div class="w-16 h-20 bg-gradient-to-b from-green-400 to-green-600 rounded flex items-center justify-center text-white font-bold text-xs">LIT</div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 mb-1">World Literature</h3>
                                <p class="text-sm text-gray-600 mb-2">by Various Authors</p>
                                <div class="flex items-center space-x-2 mb-3">
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Borrowed</span>
                                    <span class="text-xs text-gray-500">Literature</span>
                                </div>
                                <button disabled class="w-full bg-gray-400 text-white py-2 rounded-lg text-sm font-medium cursor-not-allowed">
                                    Not Available
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reservations Page -->
            <div id="reservations" class="section hidden">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">My Reservations</h2>
                
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Active Reservations</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">Advanced Mathematics</h4>
                                    <p class="text-sm text-gray-600">by James Stewart</p>
                                    <p class="text-xs text-blue-600 mt-1">Position #2 in queue</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-600">Reserved: Dec 15, 2024</p>
                                    <button class="text-red-600 hover:text-red-800 text-sm font-medium mt-2">
                                        Cancel Reservation
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">Database Systems</h4>
                                    <p class="text-sm text-gray-600">by Ramez Elmasri</p>
                                    <p class="text-xs text-green-600 mt-1">Ready for pickup!</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-600">Reserved: Dec 10, 2024</p>
                                    <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium mt-2">
                                        Pickup Book
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Penalties Page -->
            <div id="penalties" class="section hidden">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">Penalties & Clearance</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Penalty Summary -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">💰 Penalty Summary</h3>
                        <div class="text-center mb-6">
                            <div class="text-4xl font-bold text-red-600 mb-2">₱50.00</div>
                            <p class="text-gray-600">Total Outstanding Penalties</p>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">Late Return Fee</p>
                                    <p class="text-sm text-gray-600">Introduction to Computer Science</p>
                                </div>
                                <span class="text-red-600 font-medium">₱50.00</span>
                            </div>
                        </div>
                        
                        <button class="w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded-lg font-medium mt-6 transition-colors">
                            Pay Penalties
                        </button>
                    </div>
                    
                    <!-- Clearance Status -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">📋 Clearance Status</h3>
                        <div class="text-center mb-6">
                            <div class="text-6xl mb-4">❌</div>
                            <p class="text-red-600 font-medium text-lg">Not Cleared</p>
                            <p class="text-gray-600 text-sm">Outstanding penalties must be paid</p>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700">Books Returned</span>
                                <span class="text-green-600">✅</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700">Penalties Paid</span>
                                <span class="text-red-600">❌</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700">Account Status</span>
                                <span class="text-red-600">❌</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Book Modal -->
    <div id="addBookModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Add New Book</h3>
            <form onsubmit="addBook(event)" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Author</label>
                    <input type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ISBN</label>
                    <input type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Category</option>
                        <option value="computer-science">Computer Science</option>
                        <option value="mathematics">Mathematics</option>
                        <option value="literature">Literature</option>
                        <option value="science">Science</option>
                    </select>
                </div>
                <div class="flex space-x-3 pt-4">
                    <button type="button" onclick="hideAddBookModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        Add Book
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentUser = null;

        // Demo users
        const users = {
            'student@uni.edu': { name: 'John Smith', role: 'Student', password: 'password' },
            'teacher@uni.edu': { name: 'Dr. Sarah Johnson', role: 'Teacher', password: 'password' },
            'librarian@uni.edu': { name: 'Mary Wilson', role: 'Librarian', password: 'password' },
            'staff@uni.edu': { name: 'Mike Davis', role: 'Staff', password: 'password' }
        };

        // Navigation menus for each role
        const roleNavigation = {
            'Student': [
                { id: 'studentDashboard', label: '📊 Dashboard', default: true },
                { id: 'bookInventory', label: '🔖 Reserve Book' },
                { id: 'reservations', label: '📋 My Reservations' },
                { id: 'penalties', label: '💰 Penalties & Clearance' }
            ],
            'Teacher': [
                { id: 'teacherDashboard', label: '📊 Dashboard', default: true },
                { id: 'bookInventory', label: '🔖 Reserve Book' },
                { id: 'reservations', label: '📋 My Reservations' },
                { id: 'penalties', label: '💰 Clearance Status' }
            ],
            'Librarian': [
                { id: 'librarianDashboard', label: '📊 Dashboard', default: true },
                { id: 'bookInventory', label: '➕ Add Book' },
                { id: 'bookInventory', label: '✏️ Update Book' },
                { id: 'bookInventory', label: '📦 Archive Books' },
                { id: 'bookInventory', label: '📊 Manage Inventory' }
            ],
            'Staff': [
                { id: 'staffDashboard', label: '📊 Dashboard', default: true },
                { id: 'staffDashboard', label: '📝 Facilitate Borrowing' },
                { id: 'staffDashboard', label: '📥 Facilitate Returning' },
                { id: 'staffDashboard', label: '👤 View Borrower Status' },
                { id: 'penalties', label: '💰 Calculate Penalties' }
            ]
        };

        function handleLogin(event) {
            event.preventDefault();
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;

            if (users[email] && users[email].password === password) {
                currentUser = users[email];
                showMainApp();
            } else {
                alert('Invalid credentials. Please try the demo accounts provided.');
            }
        }

        function showMainApp() {
            document.getElementById('loginPage').classList.add('hidden');
            document.getElementById('mainApp').classList.remove('hidden');
            
            // Set user info
            document.getElementById('userName').textContent = currentUser.name;
            document.getElementById('userRole').textContent = currentUser.role;
            
            // Setup navigation
            setupNavigation();
            
            // Show default dashboard
            showDefaultDashboard();
        }

        function setupNavigation() {
            const navMenu = document.getElementById('navMenu');
            const navigation = roleNavigation[currentUser.role];
            
            navMenu.innerHTML = '';
            
            navigation.forEach((nav, index) => {
                const button = document.createElement('button');
                button.onclick = () => showSection(nav.id);
                button.className = `nav-btn font-medium pb-2 px-1 transition-colors ${
                    index === 0 ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-blue-600'
                }`;
                button.textContent = nav.label;
                navMenu.appendChild(button);
            });
        }

        function showDefaultDashboard() {
            const dashboardId = currentUser.role.toLowerCase() + 'Dashboard';
            showSection(dashboardId);
        }

        function showSection(sectionId) {
            // Hide all sections and dashboards
            document.querySelectorAll('.section, .dashboard').forEach(section => {
                section.classList.add('hidden');
            });
            
            // Show selected section
            const section = document.getElementById(sectionId);
            if (section) {
                section.classList.remove('hidden');
                section.classList.add('fade-in');
            }
            
            // Update navigation
            document.querySelectorAll('.nav-btn').forEach(btn => {
                btn.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600');
                btn.classList.add('text-gray-600');
            });
            
            if (event && event.target) {
                event.target.classList.remove('text-gray-600');
                event.target.classList.add('text-blue-600', 'border-b-2', 'border-blue-600');
            }
        }

        function logout() {
            currentUser = null;
            document.getElementById('mainApp').classList.add('hidden');
            document.getElementById('loginPage').classList.remove('hidden');
            document.getElementById('loginEmail').value = '';
            document.getElementById('loginPassword').value = '';
        }

        function showForgotPassword() {
            alert('Password reset functionality would be implemented here. For demo purposes, use the provided credentials.');
        }

        function showRegistrationPage() {
            document.getElementById('loginPage').classList.add('hidden');
            document.getElementById('registrationPage').classList.remove('hidden');
        }

        function showLoginPage() {
            document.getElementById('registrationPage').classList.add('hidden');
            document.getElementById('loginPage').classList.remove('hidden');
        }

        function handleRegistration(event) {
            event.preventDefault();
            
            const fullName = document.getElementById('regFullName').value;
            const email = document.getElementById('regEmail').value;
            const id = document.getElementById('regId').value;
            const role = document.getElementById('regRole').value;
            const phone = document.getElementById('regPhone').value;
            const password = document.getElementById('regPassword').value;
            const confirmPassword = document.getElementById('regConfirmPassword').value;
            
            // Validate passwords match
            if (password !== confirmPassword) {
                alert('Passwords do not match. Please try again.');
                return;
            }
            
            // Validate password strength (basic)
            if (password.length < 6) {
                alert('Password must be at least 6 characters long.');
                return;
            }
            
            // Check if email already exists (in demo)
            if (users[email]) {
                alert('An account with this email already exists. Please use a different email or sign in.');
                return;
            }
            
            // In a real system, this would send data to a server
            // For demo purposes, we'll show a success message
            alert(`Registration submitted successfully!\n\nAccount Details:\nName: ${fullName}\nEmail: ${email}\nRole: ${role}\n\nPlease visit the library with a valid ID for account verification and activation. You will receive an email once your account is approved.`);
            
            // Clear form and return to login
            event.target.reset();
            showLoginPage();
        }

        // Modal functions
        function showAddBookModal() {
            document.getElementById('addBookModal').classList.remove('hidden');
            document.getElementById('addBookModal').classList.add('flex');
        }

        function hideAddBookModal() {
            document.getElementById('addBookModal').classList.add('hidden');
            document.getElementById('addBookModal').classList.remove('flex');
        }

        function addBook(event) {
            event.preventDefault();
            alert('Book added successfully! (This is a demo - in a real system, this would save to a database)');
            hideAddBookModal();
            event.target.reset();
        }

        // Close modal when clicking outside
        document.getElementById('addBookModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideAddBookModal();
            }
        });
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'98b346a9565e04c9',t:'MTc1OTkwMTczOC4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>


