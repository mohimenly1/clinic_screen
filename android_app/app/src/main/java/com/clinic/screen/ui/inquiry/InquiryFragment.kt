package com.clinic.screen.ui.inquiry

import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.util.Log
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.view.animation.AccelerateDecelerateInterpolator
import androidx.core.view.isVisible
import androidx.fragment.app.Fragment
import androidx.fragment.app.activityViewModels
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.GridLayoutManager
import androidx.recyclerview.widget.LinearLayoutManager
import android.app.Dialog
import android.view.Window
import android.widget.Button
import android.widget.TextView
import android.widget.Toast
import com.clinic.screen.data.model.Department
import com.clinic.screen.data.model.Doctor
import com.clinic.screen.databinding.FragmentInquiryBinding
import com.clinic.screen.ui.inquiry.adapters.DepartmentsAdapter
import com.clinic.screen.ui.inquiry.adapters.DoctorsAdapter
import com.clinic.screen.ui.inquiry.adapters.ScheduleItemAdapter
import com.clinic.screen.ui.inquiry.DaysAdapter
import com.clinic.screen.ui.main.viewmodel.MainViewModel
import kotlinx.coroutines.launch

/**
 * Inquiry Fragment - Displays departments, doctors, and schedules
 * Adapts layout based on screen orientation (portrait/landscape)
 */
class InquiryFragment : Fragment() {

    private var _binding: FragmentInquiryBinding? = null
    private val binding get() = _binding!!

    private val viewModel: MainViewModel by activityViewModels()

    private var currentStep = InquiryStep.DEPARTMENTS
    private var selectedDepartment: Department? = null
    private var selectedDoctor: Doctor? = null

    private var inactivityHandler: Handler? = null
    private var inactivityRunnable: Runnable? = null
    private val INACTIVITY_TIMEOUT = 15000L // 15 seconds

    enum class InquiryStep {
        DEPARTMENTS, DOCTORS, DETAILS
    }

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentInquiryBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        // Start with fade in animation
        binding.root.alpha = 0f
        binding.root.animate()
            .alpha(1f)
            .setDuration(300)
            .start()

        setupObservers()
        setupInactivityTimer()
        setupCloseButton()
        setupBackButton()
        
        // Load departments if not already loaded
        if (viewModel.departments.value.isEmpty()) {
            viewModel.loadDepartments()
        }
    }

    private fun setupObservers() {
        // Observe screen orientation to adapt layout
        lifecycleScope.launch {
            viewModel.screenOrientation.collect { orientation ->
                updateLayoutForOrientation(orientation)
            }
        }

        // Observe departments data
        lifecycleScope.launch {
            viewModel.departments.collect { departments ->
                if (departments.isNotEmpty()) {
                    showDepartments(departments)
                }
            }
        }
    }

    private fun updateLayoutForOrientation(orientation: String) {
        val isLandscape = orientation.lowercase() == "landscape"
        
        // Update content layout based on orientation
        updateContentLayout(isLandscape)
    }

    private fun updateContentLayout(isLandscape: Boolean) {
        when (currentStep) {
            InquiryStep.DEPARTMENTS -> {
                // Grid layout for departments
                val spanCount = if (isLandscape) 4 else 2
                binding.contentRecyclerView.layoutManager = GridLayoutManager(requireContext(), spanCount)
                // Scroll to top when switching views
                binding.contentRecyclerView.post {
                    binding.contentRecyclerView.scrollToPosition(0)
                }
            }
            InquiryStep.DOCTORS -> {
                // Grid layout for doctors
                val spanCount = if (isLandscape) 3 else 2
                binding.contentRecyclerView.layoutManager = GridLayoutManager(requireContext(), spanCount)
                // Scroll to top when switching views
                binding.contentRecyclerView.post {
                    binding.contentRecyclerView.scrollToPosition(0)
                }
            }
            InquiryStep.DETAILS -> {
                // Grid layout for days (with calendar icons)
                val spanCount = if (isLandscape) 4 else 2
                binding.contentRecyclerView.layoutManager = GridLayoutManager(requireContext(), spanCount)
                // Scroll to top when switching views
                binding.contentRecyclerView.post {
                    binding.contentRecyclerView.scrollToPosition(0)
                }
            }
        }
    }

    private fun showDepartments(departments: List<Department>) {
        currentStep = InquiryStep.DEPARTMENTS
        selectedDepartment = null
        selectedDoctor = null

        updateHeader("اختر القسم", "استعلام سريع عن معلومات الأطباء والمواعيد")
        binding.contentRecyclerView.adapter = DepartmentsAdapter(departments) { department: Department ->
            selectDepartment(department)
        }
        binding.backButtonCard.isVisible = false
        resetInactivityTimer()
    }

    fun selectDepartment(department: Department) {
        selectedDepartment = department
        currentStep = InquiryStep.DOCTORS

        updateHeader(department.name, "${department.doctors?.size ?: 0} طبيب")
        binding.contentRecyclerView.adapter = DoctorsAdapter(department.doctors ?: emptyList()) { doctor: Doctor ->
            selectDoctor(doctor)
        }
        binding.backButtonCard.isVisible = true
        resetInactivityTimer()
    }

    fun selectDoctor(doctor: Doctor) {
        selectedDoctor = doctor
        currentStep = InquiryStep.DETAILS

        updateHeader(doctor.name, "${doctor.schedules?.size ?: 0} موعد")
        
        // Show days with schedules
        val schedules = doctor.schedules ?: emptyList()
        val dayGroups = createDayGroups(schedules)
        binding.contentRecyclerView.adapter = DaysAdapter(dayGroups) { dayGroup ->
            showSchedulesDialog(dayGroup)
        }
        
        binding.backButtonCard.isVisible = true
        resetInactivityTimer()
    }
    
    private fun createDayGroups(schedules: List<com.clinic.screen.data.model.Schedule>): List<DaysAdapter.DayScheduleGroup> {
        val dayOrder = listOf("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday")
        val dayLabels = mapOf(
            "Sunday" to "الأحد",
            "Monday" to "الإثنين",
            "Tuesday" to "الثلاثاء",
            "Wednesday" to "الأربعاء",
            "Thursday" to "الخميس",
            "Friday" to "الجمعة",
            "Saturday" to "السبت"
        )
        
        return schedules
            .groupBy { it.dayOfWeek }
            .map { (day, daySchedules) ->
                DaysAdapter.DayScheduleGroup(
                    day = day,
                    dayLabel = dayLabels[day] ?: day,
                    schedules = daySchedules.sortedBy { it.startTime }
                )
            }
            .sortedBy { dayOrder.indexOf(it.day) }
    }
    
    private fun showSchedulesDialog(dayGroup: DaysAdapter.DayScheduleGroup) {
        val dialog = Dialog(requireContext())
        dialog.requestWindowFeature(Window.FEATURE_NO_TITLE)
        
        val dialogView = LayoutInflater.from(requireContext())
            .inflate(com.clinic.screen.R.layout.dialog_schedules, null)
        
        val dayHeader: TextView = dialogView.findViewById(com.clinic.screen.R.id.dayHeader)
        val schedulesRecyclerView: androidx.recyclerview.widget.RecyclerView = 
            dialogView.findViewById(com.clinic.screen.R.id.schedulesRecyclerView)
        val closeButton: Button = dialogView.findViewById(com.clinic.screen.R.id.closeButton)
        
        dayHeader.text = dayGroup.dayLabel
        schedulesRecyclerView.layoutManager = LinearLayoutManager(requireContext())
        schedulesRecyclerView.adapter = ScheduleItemAdapter(dayGroup.schedules)
        
        closeButton.setOnClickListener {
            dialog.dismiss()
        }
        
        dialog.setContentView(dialogView)
        dialog.window?.setLayout(
            ViewGroup.LayoutParams.MATCH_PARENT,
            ViewGroup.LayoutParams.WRAP_CONTENT
        )
        dialog.window?.setBackgroundDrawableResource(android.R.color.transparent)
        dialog.show()
    }

    private fun updateHeader(title: String, subtitle: String) {
        binding.headerTitle.text = title
        binding.headerSubtitle.text = subtitle
    }

    private fun setupCloseButton() {
        binding.closeButton.setOnClickListener {
            closeInquiry()
        }
    }

    private fun setupBackButton() {
        binding.backButton.setOnClickListener {
            goBack()
        }
        // Make the entire card clickable
        binding.backButtonCard.setOnClickListener {
            goBack()
        }
    }

    private fun goBack() {
        when (currentStep) {
            InquiryStep.DETAILS -> {
                selectedDoctor?.let { doctor ->
                    selectedDepartment?.let { dept ->
                        selectDepartment(dept)
                    }
                }
            }
            InquiryStep.DOCTORS -> {
                showDepartments(viewModel.departments.value)
            }
            InquiryStep.DEPARTMENTS -> {
                closeInquiry()
            }
        }
        resetInactivityTimer()
    }

    private fun closeInquiry() {
        // Fade out animation
        binding.root.animate()
            .alpha(0f)
            .setDuration(300)
            .setInterpolator(AccelerateDecelerateInterpolator())
            .withEndAction {
                parentFragmentManager.beginTransaction()
                    .remove(this)
                    .commit()
            }
            .start()
    }

    private fun setupInactivityTimer() {
        inactivityHandler = Handler(Looper.getMainLooper())
        inactivityRunnable = Runnable {
            closeInquiry()
        }
    }

    private fun resetInactivityTimer() {
        inactivityHandler?.removeCallbacks(inactivityRunnable ?: return)
        inactivityHandler?.postDelayed(inactivityRunnable ?: return, INACTIVITY_TIMEOUT)
    }

    override fun onDestroyView() {
        super.onDestroyView()
        inactivityHandler?.removeCallbacks(inactivityRunnable ?: return)
        inactivityHandler = null
        inactivityRunnable = null
        _binding = null
    }

    companion object {
        private const val TAG = "InquiryFragment"
    }
}

