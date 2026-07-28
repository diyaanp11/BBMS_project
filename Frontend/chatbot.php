<!-- AI Chatbot Widget -->
<style>
    .chatbot-toggle {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #d32f2f 0%, #b71c1c 100%);
        border-radius: 50%;
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(211, 47, 47, 0.4);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    .chatbot-toggle:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(211, 47, 47, 0.5);
    }
    .chatbot-container {
        position: fixed;
        bottom: 90px;
        right: 20px;
        width: 350px;
        max-width: calc(100vw - 40px);
        height: 450px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        z-index: 9999;
        display: none;
        flex-direction: column;
        overflow: hidden;
        animation: slideUp 0.3s ease;
    }
    .chatbot-container.active {
        display: flex;
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .chatbot-header {
        background: linear-gradient(135deg, #d32f2f 0%, #b71c1c 100%);
        color: white;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .chatbot-header h4 {
        margin: 0;
        font-size: 16px;
    }
    .chatbot-header span {
        font-size: 12px;
        opacity: 0.9;
    }
    .chatbot-close {
        background: none;
        border: none;
        color: white;
        font-size: 20px;
        cursor: pointer;
    }
    .chatbot-messages {
        flex: 1;
        overflow-y: auto;
        padding: 15px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .chat-message {
        max-width: 85%;
        padding: 10px 14px;
        border-radius: 15px;
        font-size: 14px;
        line-height: 1.4;
        animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .chat-message.bot {
        background: #f1f1f1;
        color: #333;
        align-self: flex-start;
        border-bottom-left-radius: 5px;
    }
    .chat-message.user {
        background: #d32f2f;
        color: white;
        align-self: flex-end;
        border-bottom-right-radius: 5px;
    }
    .chatbot-input {
        display: flex;
        padding: 12px;
        border-top: 1px solid #eee;
        gap: 8px;
    }
    .chatbot-input input {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 25px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.3s;
    }
    .chatbot-input input:focus {
        border-color: #d32f2f;
    }
    .chatbot-input button {
        width: 40px;
        height: 40px;
        background: #d32f2f;
        border: none;
        border-radius: 50%;
        color: white;
        cursor: pointer;
        font-size: 16px;
        transition: background 0.3s;
    }
    .chatbot-input button:hover {
        background: #b71c1c;
    }
    .quick-replies {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        padding: 0 15px 10px;
    }
    .quick-reply {
        background: #f1f1f1;
        border: 1px solid #ddd;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .quick-reply:hover {
        background: #d32f2f;
        color: white;
        border-color: #d32f2f;
    }
    @media (max-width: 480px) {
        .chatbot-container {
            bottom: 0;
            right: 0;
            width: 100%;
            max-width: 100%;
            height: 100vh;
            border-radius: 0;
        }
    }
</style>

<button class="chatbot-toggle" id="chatToggle" title="Chat with BBMS Assistant">
    <i class="fas fa-comments"></i>
</button>

<div class="chatbot-container" id="chatContainer">
    <div class="chatbot-header">
        <div>
            <h4><i class="fas fa-robot"></i> BBMS Assistant</h4>
            <span>Ask me anything about blood donation!</span>
        </div>
        <button class="chatbot-close" id="chatClose">&times;</button>
    </div>
    
    <div class="chatbot-messages" id="chatMessages">
        <div class="chat-message bot">
            Hello! 👋 I'm your BBMS Assistant. I can help you with:
            <br><br>
            • Blood donation eligibility<br>
            • Blood types & compatibility<br>
            • Donation process<br>
            • How to request blood<br>
            <br>
            Type your question below!
        </div>
    </div>
    
    <div class="quick-replies">
        <span class="quick-reply" data-msg="Who can donate blood?">Who can donate?</span>
        <span class="quick-reply" data-msg="What are blood types?">Blood types</span>
        <span class="quick-reply" data-msg="How to request blood?">Request blood</span>
        <span class="quick-reply" data-msg="Is donation safe?">Is it safe?</span>
    </div>
    
    <div class="chatbot-input">
        <input type="text" id="chatInput" placeholder="Type your message..." maxlength="200">
        <button id="chatSend"><i class="fas fa-paper-plane"></i></button>
    </div>
</div>

<script>
(function() {
    const chatToggle = document.getElementById('chatToggle');
    const chatContainer = document.getElementById('chatContainer');
    const chatClose = document.getElementById('chatClose');
    const chatMessages = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    const chatSend = document.getElementById('chatSend');
    const quickReplies = document.querySelectorAll('.quick-reply');

    // Knowledge base for the chatbot
    const knowledgeBase = {
        'who can donate': "Generally, healthy individuals aged 18-65, weighing at least 50kg can donate blood. You should not be pregnant or breastfeeding, and must be in good health. You can register as a donor from the Donor section!",
        'how often': "You can donate whole blood every 56 days (8 weeks). Platelet donations can be made every 7 days, up to 24 times per year. The body replaces donated blood within 24-48 hours!",
        'blood types': "There are 8 main blood types: A+, A-, B+, B-, AB+, AB-, O+, O-. O- is the universal donor (can give to anyone). AB+ is the universal recipient (can receive from anyone).",
        'universal donor': "O- (O negative) is the universal donor. People with O- blood can donate to anyone, regardless of blood type. Only about 7% of people have this blood type!",
        'universal recipient': "AB+ (AB positive) is the universal recipient. People with AB+ blood can receive blood from any donor. However, they can only donate to other AB+ people.",
        'is safe': "Yes! Blood donation is completely safe. We use sterile, single-use equipment for every donation. The process takes only 8-10 minutes, and the actual needle insertion feels like a slight pinch — less painful than a bee sting!",
        'how long': "The entire donation process takes about 45-60 minutes: 10 min registration, 5 min health screening, 8-10 min actual donation, and rest time with refreshments.",
        'process': "The donation process has 5 steps:\n1. Registration (fill form, show ID)\n2. Health screening (questionnaire + checkup)\n3. Donation (8-10 minutes, 450ml)\n4. Rest & refreshments\n5. Receive your donor certificate",
        'request blood': "To request blood:\n1. Register as a Recipient on our website\n2. Go to your dashboard\n3. Click 'Request Blood'\n4. Fill in patient details, hospital, blood type needed\n5. Upload any medical documents\n6. Submit — Admin will review your request!",
        'how to register': "To register:\n1. Click 'Login' in the navigation\n2. Choose your role (Admin, Donor, or Recipient)\n3. Fill in your details\n4. For Donors: name, email, password, blood type\n5. For Recipients: name, email, password, location",
        'benefits': "Health benefits of blood donation include:\n• Burns ~650 calories per donation\n• Reduces heart disease risk by 30%\n• Lowers cholesterol\n• Stimulates new blood cell production\n• Free health screening included\n• Reduces iron overload",
        'myths': "Common myths debunked:\n❌ Myth: Donating is painful → ✅ Fact: Only a slight pinch\n❌ Myth: I can get infected → ✅ Fact: Sterile, single-use equipment every time\n❌ Myth: It makes me weak → ✅ Fact: Body replaces blood in 24-48 hours\n❌ Myth: Takes too long → ✅ Fact: Only 45-60 minutes total",
        'eligibility': "Eligibility criteria:\n• Age: 18-65 years\n• Weight: Minimum 50 kg\n• Hemoglobin: Minimum 12.5 g/dL\n• No illness for past 2 weeks\n• 56 days since last donation\n• Not pregnant or breastfeeding",
        'tattoos': "Yes, you can donate blood if you have tattoos! You need to wait 6 months after getting a tattoo before donating. This is a safety precaution to ensure no infections.",
        'covid': "Yes, blood donation is safe during COVID-19! Blood banks follow strict safety protocols including sanitization, social distancing, and screening procedures.",
        'blood lasts': "How long donated blood lasts:\n• Red blood cells: 42 days\n• Platelets: 5 days\n• Plasma: up to 1 year\nThis is why regular donations are so important!",
        'emergency': "Blood is needed in emergencies for:\n• Accident & trauma victims\n• Surgery patients\n• Cancer treatments\n• Childbirth complications\n• Burn victims\n• Anemia patients",
        'contact': "You can contact our team through the Contact Us page! You'll find email addresses and social media links for Diya Sharma and Kabita Pandey.",
        'about': "BBMS (Blood Bank Management System) connects blood donors with recipients. We have three user roles: Admin (manages everything), Donor (donates blood), and Recipient (requests blood). Built with PHP & MySQL!",
        'help': "I can help with:\n• Who can donate blood?\n• Blood types & compatibility\n• Donation process & benefits\n• How to request blood\n• Eligibility criteria\n• Common myths debunked\n\nJust type your question!",
        'default': "I'm not sure about that specific question. Try asking about:\n• Blood donation eligibility\n• Blood types\n• How to donate or request blood\n• Donation benefits\n• Safety information\n\nOr visit our Learn More page for detailed information!"
    };

    function getResponse(message) {
        const msg = message.toLowerCase().trim();
        
        // Check for keyword matches
        for (const [key, value] of Object.entries(knowledgeBase)) {
            if (key !== 'default' && msg.includes(key)) {
                return value;
            }
        }
        
        // Additional keyword checks
        if (msg.includes('donat')) return knowledgeBase['who can donate'];
        if (msg.includes('type') || msg.includes('ab') || msg.includes('o-') || msg.includes('compatib')) return knowledgeBase['blood types'];
        if (msg.includes('safe') || msg.includes('pain') || msg.includes('hurt')) return knowledgeBase['is safe'];
        if (msg.includes('time') || msg.includes('long') || msg.includes('minute')) return knowledgeBase['how long'];
        if (msg.includes('request') || msg.includes('need blood') || msg.includes('emergency')) return knowledgeBase['request blood'];
        if (msg.includes('register') || msg.includes('sign up') || msg.includes('account')) return knowledgeBase['how to register'];
        if (msg.includes('benefit') || msg.includes('health')) return knowledgeBase['benefits'];
        if (msg.includes('myth') || msg.includes('fact')) return knowledgeBase['myths'];
        if (msg.includes('eligible') || msg.includes('criteria') || msg.includes('requirement')) return knowledgeBase['eligibility'];
        if (msg.includes('how often') || msg.includes('frequency') || msg.includes('interval')) return knowledgeBase['how often'];
        if (msg.includes('tattoo') || msg.includes('piercing')) return knowledgeBase['tattoos'];
        if (msg.includes('covid') || msg.includes('pandemic') || msg.includes('virus')) return knowledgeBase['covid'];
        if (msg.includes('last') || msg.includes('expire') || msg.includes('storage') || msg.includes('shelf')) return knowledgeBase['blood lasts'];
        if (msg.includes('when') || msg.includes('need') || msg.includes('hospital')) return knowledgeBase['emergency'];
        if (msg.includes('contact') || msg.includes('email') || msg.includes('phone')) return knowledgeBase['contact'];
        if (msg.includes('about') || msg.includes('bbms') || msg.includes('project') || msg.includes('system')) return knowledgeBase['about'];
        if (msg.includes('hi') || msg.includes('hello') || msg.includes('hey') || msg.includes('namaste')) return "Hello! 👋 Welcome to BBMS! How can I help you today? You can ask me about blood donation, blood types, eligibility, or how to use this system!";
        if (msg.includes('thank')) return "You're welcome! 😊 Feel free to ask more questions anytime. Together we can save lives through blood donation!";
        if (msg.includes('bye') || msg.includes('goodbye')) return "Goodbye! 👋 Remember, your blood donation can save up to 3 lives. Consider registering as a donor today!";
        
        return knowledgeBase['default'];
    }

    function addMessage(text, type) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `chat-message ${type}`;
        msgDiv.textContent = text;
        chatMessages.appendChild(msgDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function sendMessage() {
        const text = chatInput.value.trim();
        if (!text) return;
        
        addMessage(text, 'user');
        chatInput.value = '';
        
        // Simulate typing delay
        setTimeout(() => {
            const response = getResponse(text);
            addMessage(response, 'bot');
        }, 500);
    }

    // Event listeners
    chatToggle.addEventListener('click', () => {
        chatContainer.classList.toggle('active');
        if (chatContainer.classList.contains('active')) {
            chatInput.focus();
        }
    });

    chatClose.addEventListener('click', () => {
        chatContainer.classList.remove('active');
    });

    chatSend.addEventListener('click', sendMessage);
    
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });

    quickReplies.forEach(btn => {
        btn.addEventListener('click', () => {
            chatInput.value = btn.dataset.msg;
            sendMessage();
        });
    });
})();
</script>
