<div id="flashMessage" class="fixed z-[100] p-4 shadow-lg 
                transition-all duration-300 ease-out 
                transform opacity-0
                
                top-0 left-0 right-0 rounded-none translate-y-[-100%]

                sm:top-5 sm:right-5 sm:left-auto sm:max-w-sm sm:rounded-lg sm:translate-y-0 sm:translate-x-full"
  role="alert">

  <div class="flex items-center">
    <svg id="flash-icon-success" class="hidden h-6 w-6 text-green-500 mr-3" xmlns="http://www.w3.org/2000/svg"
      fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
    </svg>

    <svg id="flash-icon-error" class="hidden h-6 w-6 text-red-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none"
      viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
    </svg>

    <span id="flashMessageText" class="font-medium"></span>
  </div>
</div>