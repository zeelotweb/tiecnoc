<section class="legal-page partner-page">
    <div class="legal-container">

        <div class="legal-header">
            <span class="legal-label">Partnership / 001</span>
            <h1 class="legal-title">Be A Partner</h1>
            <p class="legal-meta">Coming Soon &nbsp;&middot;&nbsp; Expression of Interest Now Open</p>
        </div>

        <div class="legal-body">

            <div class="legal-intro">
                <p>We're building something worth sharing. The Tiecnoc Partner Program is in development &mdash; a commission-based structure designed for individuals who believe in the brand and want to grow with it.</p>
            </div>

            <div class="legal-section">
                <h2>What to Expect</h2>
                <p>As a Tiecnoc Partner, you'll receive a unique referral link tied to your account. Every sale driven through your link earns you a commission. No inventory. No upfront cost. Just the collection, your audience, and a split on every piece that moves.</p>
                <p>Details on commission rates, payout structure, and program tiers will be released when the program officially launches.</p>
            </div>

            <div class="legal-section">
                <h2>Who It's For</h2>
                <p>Whether you're a content creator, stylist, or someone with a community that appreciates quality essentials &mdash; if you wear it and believe in it, the program is designed for you.</p>
            </div>

            <div class="legal-section partner-interest">
                <h2>Register Your Interest</h2>
                <p>Drop your email below to be notified when the Partner Program goes live. Early expressions of interest will be considered for founding partner status.</p>

                @if(session('partner_success'))
                    <div class="support-alert support-alert--success">
                        {{ session('partner_success') }}
                    </div>
                @endif

                <form action="{{ route('partner.interest') }}" method="POST" class="support-form partner-form">
                    @csrf

                    <div class="support-field">
                        <label for="partner_name">Name</label>
                        <input type="text" name="name" id="partner_name" placeholder="Your name" value="{{ old('name') }}" required>
                        @error('name') <span class="support-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="support-field">
                        <label for="partner_email">Email</label>
                        <input type="email" name="email" id="partner_email" placeholder="your@email.com" value="{{ old('email') }}" required>
                        @error('email') <span class="support-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="support-field">
                        <label for="partner_platform">Your Platform or Audience <span class="support-optional">(optional)</span></label>
                        <input type="text" name="platform" id="partner_platform" placeholder="e.g. Instagram, TikTok, blog, local community..." value="{{ old('platform') }}">
                    </div>

                    <button type="submit" class="support-submit">Register Interest</button>
                </form>
            </div>

        </div>

    </div>
</section>
