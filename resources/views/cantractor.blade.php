<section class="legal-page contractor-page">
    <div class="legal-container">

        <div class="legal-header">
            <span class="legal-label">Partnership / 002</span>
            <h1 class="legal-title">Become a Contractor</h1>
            <p class="legal-meta">Coming Soon &nbsp;&middot;&nbsp; Vendor &amp; Contractor Portal in Development</p>
        </div>

        <div class="legal-body">

            <div class="legal-intro">
                <p>Tiecnoc is building a dedicated portal for vendors, manufacturers, and service contractors. If you work in production, logistics, creative services, or supply chain and see alignment with what we're building &mdash; this space is for you.</p>
            </div>

            <div class="legal-section">
                <h2>What's Coming</h2>
                <p>The Contractor Portal will provide a structured channel for potential partners to submit proposals, engage on service agreements, and connect directly with the Tiecnoc team. We're building this to be efficient and direct &mdash; no unnecessary back and forth.</p>
            </div>

            <div class="legal-section">
                <h2>Who We're Looking For</h2>
                <p>We are interested in working with:</p>
                <ul>
                    <li>Garment manufacturers and production houses</li>
                    <li>Fabric and material suppliers</li>
                    <li>Fulfillment and logistics partners</li>
                    <li>Photographers and creative production teams</li>
                    <li>Packaging and branding vendors</li>
                </ul>
                <p>If your capabilities align with where we're going, we want to hear from you.</p>
            </div>

            <div class="legal-section partner-interest">
                <h2>Notify Me When It Launches</h2>
                <p>The portal is under development. Leave your details and we'll reach out when it's ready.</p>

                @if(session('contractor_success'))
                    <div class="support-alert support-alert--success">
                        {{ session('contractor_success') }}
                    </div>
                @endif

                <form action="{{ route('contractor.interest') }}" method="POST" class="support-form contractor-form">
                    @csrf

                    <div class="support-field">
                        <label for="contractor_name">Name</label>
                        <input type="text" name="name" id="contractor_name" placeholder="Your name" value="{{ old('name') }}" required>
                        @error('name') <span class="support-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="support-field">
                        <label for="contractor_email">Email</label>
                        <input type="email" name="email" id="contractor_email" placeholder="your@email.com" value="{{ old('email') }}" required>
                        @error('email') <span class="support-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="support-field">
                        <label for="contractor_company">Company / Business Name <span class="support-optional">(optional)</span></label>
                        <input type="text" name="company" id="contractor_company" placeholder="Your company or trade name" value="{{ old('company') }}">
                    </div>

                    <div class="support-field">
                        <label for="contractor_service">Service or Specialty</label>
                        <input type="text" name="service" id="contractor_service" placeholder="e.g. Garment production, logistics, photography..." value="{{ old('service') }}" required>
                        @error('service') <span class="support-error">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="support-submit">Submit Details</button>
                </form>
            </div>

        </div>

    </div>
</section>
