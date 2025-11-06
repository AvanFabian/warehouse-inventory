<x-app-layout>
   <div class="p-4 md:p-6">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
         <h1 class="text-2xl font-bold text-gray-800">Add New User</h1>
         <a href="{{ route('users.index') }}"
            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
            Back to Users
         </a>
      </div>

      <!-- Form -->
      <div class="bg-white rounded-lg shadow-md p-6">
         <form action="{{ route('users.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <!-- Name -->
               <div>
                  <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                  <input type="text" name="name" id="name" value="{{ old('name') }}" required
                     class="w-full border-gray-300 rounded-lg @error('name') border-red-500 @enderror">
                  @error('name')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
               </div>

               <!-- Email -->
               <div>
                  <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                  <input type="email" name="email" id="email" value="{{ old('email') }}" required
                     class="w-full border-gray-300 rounded-lg @error('email') border-red-500 @enderror">
                  @error('email')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
               </div>

               <!-- Password -->
               <div>
                  <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                  <input type="password" name="password" id="password" required
                     class="w-full border-gray-300 rounded-lg @error('password') border-red-500 @enderror">
                  @error('password')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
                  <p class="text-sm text-gray-500 mt-1">Minimum 8 characters</p>
               </div>

               <!-- Password Confirmation -->
               <div>
                  <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm
                     Password *</label>
                  <input type="password" name="password_confirmation" id="password_confirmation" required
                     class="w-full border-gray-300 rounded-lg">
               </div>

               <!-- Role -->
               <div>
                  <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                  <select name="role" id="role" required
                     class="w-full border-gray-300 rounded-lg @error('role') border-red-500 @enderror">
                     <option value="">-- Select Role --</option>
                     <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                     <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                     <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                  </select>
                  @error('role')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
               </div>

               <!-- Is Active -->
               <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                  <div class="flex items-center">
                     <input type="checkbox" name="is_active" id="is_active" value="1"
                        {{ old('is_active', true) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-primary focus:ring-primary">
                     <label for="is_active" class="ml-2 text-sm text-gray-700">Active</label>
                  </div>
               </div>
            </div>

            <!-- Role Descriptions -->
            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
               <h4 class="text-sm font-semibold text-gray-800 mb-2">Role Descriptions:</h4>
               <ul class="text-sm text-gray-700 space-y-1">
                  <li><strong>Admin:</strong> Full access to all features including user management</li>
                  <li><strong>Manager:</strong> Can manage inventory, transactions, and view reports</li>
                  <li><strong>Staff:</strong> Can manage inventory and create transactions</li>
               </ul>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-2 mt-6">
               <a href="{{ route('users.index') }}"
                  class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                  Cancel
               </a>
               <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition">
                  Create User
               </button>
            </div>
         </form>
      </div>
   </div>
</x-app-layout>
