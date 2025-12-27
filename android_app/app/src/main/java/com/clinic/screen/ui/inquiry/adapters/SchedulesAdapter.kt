package com.clinic.screen.ui.inquiry.adapters

import android.util.Log
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.LinearLayout
import android.widget.TextView
import androidx.cardview.widget.CardView
import androidx.recyclerview.widget.RecyclerView
import com.clinic.screen.R
import com.clinic.screen.data.model.Schedule

class SchedulesAdapter(
    private val schedules: List<Schedule>,
    private val departmentName: String
) : RecyclerView.Adapter<SchedulesAdapter.DayGroupViewHolder>() {

    private val dayOrder = listOf("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday")
    private val dayLabels = mapOf(
        "Sunday" to "الأحد",
        "Monday" to "الإثنين",
        "Tuesday" to "الثلاثاء",
        "Wednesday" to "الأربعاء",
        "Thursday" to "الخميس",
        "Friday" to "الجمعة",
        "Saturday" to "السبت"
    )

    private val groupedSchedules: List<DayGroup> = schedules
        .groupBy { it.dayOfWeek }
        .map { (day, daySchedules) ->
            DayGroup(day, dayLabels[day] ?: day, daySchedules.sortedBy { it.startTime })
        }
        .sortedBy { dayOrder.indexOf(it.day) }

    data class DayGroup(val day: String, val dayLabel: String, val schedules: List<Schedule>)

    inner class DayGroupViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
        val dayLabelTextView: TextView = itemView.findViewById(R.id.dayLabel)
        val schedulesCountTextView: TextView = itemView.findViewById(R.id.schedulesCount)
        val schedulesContainer: LinearLayout = itemView.findViewById(R.id.schedulesContainer)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): DayGroupViewHolder {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_schedule_group, parent, false)
        return DayGroupViewHolder(view)
    }

    override fun onBindViewHolder(holder: DayGroupViewHolder, position: Int) {
        val dayGroup = groupedSchedules[position]
        
        Log.d(TAG, "Binding day group: ${dayGroup.dayLabel} with ${dayGroup.schedules.size} schedules")
        
        // Set day header
        holder.dayLabelTextView.text = dayGroup.dayLabel
        holder.schedulesCountTextView.text = "(${dayGroup.schedules.size})"
        
        // Clear existing schedule views
        holder.schedulesContainer.removeAllViews()
        
        // Add schedule items for this day
        dayGroup.schedules.forEachIndexed { index, schedule ->
            try {
                val scheduleView = LayoutInflater.from(holder.itemView.context)
                    .inflate(R.layout.item_schedule, holder.schedulesContainer, false)
                
                val timeTextView: TextView = scheduleView.findViewById(R.id.timeText)
                val clinicTextView: TextView = scheduleView.findViewById(R.id.clinicText)
                val floorTextView: TextView = scheduleView.findViewById(R.id.floorText)
                
                // Format time (HH:mm - HH:mm) - ensure it's on one line and readable
                val startTime = if (schedule.startTime.length >= 5) {
                    schedule.startTime.substring(0, 5)
                } else {
                    schedule.startTime
                }
                val endTime = if (schedule.endTime.length >= 5) {
                    schedule.endTime.substring(0, 5)
                } else {
                    schedule.endTime
                }
                
                // Set time text - ensure single line display
                val timeText = "$startTime - $endTime"
                timeTextView.text = timeText
                timeTextView.isSingleLine = true
                timeTextView.maxLines = 1
                
                // Set clinic number
                clinicTextView.text = schedule.clinicNumber
                clinicTextView.isSingleLine = true
                clinicTextView.maxLines = 1
                
                // Set floor
                floorTextView.text = "ط. ${schedule.floor}"
                floorTextView.isSingleLine = true
                floorTextView.maxLines = 1
                
                // Make sure the view is visible
                scheduleView.visibility = View.VISIBLE
                
                holder.schedulesContainer.addView(scheduleView)
                Log.d(TAG, "Added schedule $index: $timeText")
            } catch (e: Exception) {
                Log.e(TAG, "Error adding schedule view", e)
            }
        }
        
        // Make sure container is visible
        holder.schedulesContainer.visibility = View.VISIBLE
        Log.d(TAG, "Container has ${holder.schedulesContainer.childCount} children")
    }
    
    companion object {
        private const val TAG = "SchedulesAdapter"
    }

    override fun getItemCount(): Int = groupedSchedules.size
}

