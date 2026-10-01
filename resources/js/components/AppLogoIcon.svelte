<script lang="ts">
    let {
        class: className = '',
        ...rest
    }: {
        class?: string;
        [key: string]: unknown;
    } = $props();
</script>

<svg
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 64 64"
    fill="none"
    class={className}
    {...rest}
>
      <ellipse cx="32" cy="38" rx="20" ry="23" fill="currentColor" opacity="0.08"></ellipse>
      <path d="M32 54 Q13 48 11 31 Q13 18 24 21 L32 32 Z" fill="currentColor" opacity="0.2"></path>
      <path d="M32 54 Q51 48 53 31 Q51 18 40 21 L32 32 Z" fill="currentColor" opacity="0.2"></path>
      <path d="M32 54 Q13 48 11 31 Q13 18 24 21 L32 32 Z" stroke="currentColor" stroke-width="0.7" stroke-opacity="0.35" fill="none"></path>
      <path d="M32 54 Q51 48 53 31 Q51 18 40 21 L32 32 Z" stroke="currentColor" stroke-width="0.7" stroke-opacity="0.35" fill="none"></path>
      <ellipse cx="32" cy="46" rx="9" ry="11.5" fill="currentColor"></ellipse>
      <ellipse cx="32" cy="31" rx="7.5" ry="7" fill="currentColor"></ellipse>
      <circle cx="32" cy="20" r="5.5" fill="currentColor"></circle>
      <line x1="32" y1="24" x2="32" y2="57" stroke="#000" stroke-width="1" stroke-opacity="0.22"></line>
      <line x1="24" y1="43" x2="40" y2="43" stroke="#000" stroke-width="0.6" stroke-opacity="0.18" stroke-linecap="round"></line>
      <line x1="24" y1="49" x2="40" y2="49" stroke="#000" stroke-width="0.6" stroke-opacity="0.18" stroke-linecap="round"></line>
      <line x1="26" y1="54" x2="38" y2="54" stroke="#000" stroke-width="0.6" stroke-opacity="0.18" stroke-linecap="round"></line>
      <line x1="25" y1="30" x2="11" y2="26" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"></line>
      <line x1="24.5" y1="36" x2="9" y2="36" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"></line>
      <line x1="25" y1="43" x2="12" y2="49" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"></line>
      <line x1="39" y1="30" x2="53" y2="26" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"></line>
      <line x1="39.5" y1="36" x2="55" y2="36" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"></line>
      <line x1="39" y1="43" x2="52" y2="49" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"></line>
      <path d="M29 15.5 Q25 9 19 6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" fill="none"></path>
      <path d="M35 15.5 Q39 9 45 6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" fill="none"></path>
      <circle cx="19" cy="6" r="2" fill="currentColor"></circle>
      <circle cx="45" cy="6" r="2" fill="currentColor"></circle>
      <circle cx="29.5" cy="19.5" r="1.4" fill="#000" opacity="0.35"></circle>
      <circle cx="34.5" cy="19.5" r="1.4" fill="#000" opacity="0.35"></circle>
    </svg>
